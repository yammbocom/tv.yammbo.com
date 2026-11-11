<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Trailer de una pelicula o serie para la app de TV.
 *
 * GET /api/app-tv/trailer?title=&orig=&year=&type=movie|tv|auto&lang=es
 *
 * 1) TMDB: busca el titulo (original y traducido) y ordena sus videos de YouTube
 *    por tipo (trailer > teaser > clip), idioma de la app y si son oficiales.
 * 2) Si TMDB no lo tiene (series latinas, novelas, estrenos...), busca en YouTube.
 * 3) Cada video se comprueba con oEmbed: 404 = borrado o privado, 401/403 = el
 *    dueno no deja verlo fuera de YouTube. Solo se devuelven los que la app puede
 *    reproducir dentro; `keys` trae reservas por si alguno falla al cargar.
 *
 * Aciertos cacheados 7 dias; fallos 1 dia.
 */
class TrailerController extends Controller
{
    private const BASE = 'https://api.themoviedb.org/3';
    private const TTL_OK = 604800;
    private const TTL_MISS = 86400;
    private const MAX_KEYS = 3;
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

    public function __invoke(Request $r)
    {
        $title = mb_substr(trim((string) $r->query('title', '')), 0, 120);
        $orig  = mb_substr(trim((string) $r->query('orig', '')), 0, 120);
        $year  = (int) substr(preg_replace('/\D/', '', (string) $r->query('year', '')), 0, 4);
        $type  = in_array($r->query('type'), ['movie', 'tv'], true) ? $r->query('type') : 'auto';
        $lang  = in_array($r->query('lang'), ['es', 'en', 'pt'], true) ? $r->query('lang') : 'es';
        // id de TMDB ya verificado por /ficha (titulo + reparto o anio): se usa esa ficha tal cual
        $tmdbId = max(0, (int) $r->query('tmdb', 0));

        if ($title === '' && $orig === '') {
            return response()->json(['ok' => false, 'reason' => 'no_title']);
        }

        $ck = 'tv_trailer_v19_' . md5(implode('|', [$title, $orig, $year, $type, $lang, $tmdbId]));
        if (($hit = Cache::get($ck)) !== null) {
            return response()->json($hit);
        }
        $data = $this->resolve($title, $orig, $year, $type, $lang, $tmdbId);
        Cache::put($ck, $data, $data['ok'] ? self::TTL_OK : self::TTL_MISS);
        return response()->json($data);
    }

    private function resolve(string $title, string $orig, int $year, string $type, string $lang, int $tmdbId = 0): array
    {
        $title = $this->clean($title);
        $orig = $this->clean($orig);
        $key = config('services.tmdb.key');

        $fixed = null;
        if ($key && $tmdbId > 0 && $type !== 'auto') {
            $d = $this->get("/{$type}/{$tmdbId}", ['api_key' => $key, 'language' => 'es-MX']);
            if ($d && isset($d['id'])) {
                $fixed = [
                    'id'    => (int) $d['id'],
                    'title' => $d['title'] ?? $d['name'] ?? $title,
                    'orig'  => $d['original_title'] ?? $d['original_name'] ?? '',
                    'exact' => true,
                    'type'  => $type,
                    'n'     => 0,
                ];
                // el anio real (YouTube exige el anio en el titulo del video); en series solo si
                // el catalogo no lo trae (el de la temporada puede no ser el del estreno)
                $y = (int) substr((string) ($d['release_date'] ?? $d['first_air_date'] ?? ''), 0, 4);
                if ($y > 0 && ($type === 'movie' || $year === 0)) {
                    $year = $y;
                }
                if ($orig === '' && $fixed['orig'] !== '' && $this->bare($fixed['orig']) !== $this->bare($title)) {
                    $orig = $this->clean($fixed['orig']);
                }
            }
        }
        $queries = array_values(array_unique(array_filter([$orig, $title])));

        $found = [];     // [key, name, lang]
        $tmdb = null;
        if ($key) {
            foreach (($fixed ? [$fixed] : $this->candidates($key, $queries, $year, $type)) as $c) {
                $names = null;
                foreach ($this->videos($key, $c['type'], $c['id'], $lang) as $v) {
                    // TMDB a veces cuelga el trailer de otra pelicula en una ficha (T-34 -> Corazones de acero)
                    $names = $names ?? $this->filmNames($key, $c, $queries);
                    if (! $this->aboutFilm($v['name'], $names)) {
                        continue;
                    }
                    if ($this->embeddable($v['key'])) {
                        $found[] = $v;
                        $tmdb = $tmdb ?? $c;
                    }
                    if (count($found) >= self::MAX_KEYS) {
                        break 2;
                    }
                }
                if ($found) {
                    break;      // no mezclar videos de dos titulos distintos
                }
            }
        }

        // TMDB casi no etiqueta el doblaje latino (sus videos en espanol son de Espana):
        // si no hay ninguno latino se busca en YouTube y el latino va delante.
        if ($lang === 'es' && ! array_filter($found, fn ($v) => str_starts_with($v['lang'], 'es-') && $v['lang'] !== 'es-ES')) {
            $latino = $this->youtubeSearch($title ?: $orig, $orig, $year, $lang, true);
            $found = array_slice(array_merge($latino, $found), 0, self::MAX_KEYS);
        }

        if (! $found) {
            $found = $this->youtubeSearch($title ?: $orig, $orig, $year, $lang);
        }
        if (! $found) {
            return ['ok' => false, 'reason' => 'not_found'];
        }

        return [
            'ok'    => true,
            'key'   => $found[0]['key'],
            'keys'  => array_column($found, 'key'),
            'name'  => $found[0]['name'],
            'lang'  => $found[0]['lang'],
            'type'  => $tmdb['type'] ?? $type,
            'tmdb'  => $tmdb['id'] ?? null,
            'title' => $tmdb['title'] ?? $title,
        ];
    }

    /** Quita marcas de temporada/parte que el catalogo anade al nombre ("T2", "Temporada 3", "(2019)"...). */
    protected function clean(string $s): string
    {
        $s = preg_replace('/\s*[\(\[]\d{4}[\)\]]\s*$/u', '', $s);
        $s = preg_replace('/[\s:\-–|]+(T|S|Temp\.?|Temporada|Season|Parte|Part|Vol\.?|Volumen)\s*\d{1,2}\s*$/iu', '', $s);
        $s = preg_replace('/[\s:\-–|]+(Primera|Segunda|Tercera|Cuarta|Quinta)\s+temporada\s*$/iu', '', $s);
        return trim($s);
    }

    /**
     * Fichas de TMDB en orden de confianza: titulo exacto > parecido (si hay exactas,
     * las parecidas sobran: evita spin-offs), y con anio antes que sin anio (evita remakes
     * con el mismo titulo). Solo del tipo que dice la app: distingue pelicula y serie con
     * fiabilidad (programType "movie"), y mezclarlos daba la serie "Fury" (1955) para la pelicula.
     */
    protected function candidates(string $key, array $queries, int $year, string $type): array
    {
        $types = $type === 'auto' ? ['movie', 'tv'] : [$type];
        // el anio del catalogo a veces va uno por delante (Fury: 2014 en cines, 2015 en el catalogo)
        $years = $year > 0 ? [$year, $year - 1, 0] : [0];
        $cands = [];
        foreach ($years as $y) {
            foreach ($queries as $q) {
                foreach ($types as $t) {
                    $m = $this->search($key, $t, $q, $y, $year);
                    // sin anio en la busqueda solo vale el titulo exacto: el primer parecido suele ser otra
                    if ($m && $y === 0 && $year > 0 && ! $m['exact']) {
                        continue;
                    }
                    if ($m && ! isset($cands[$t . $m['id']])) {
                        $cands[$t . $m['id']] = $m + ['type' => $t, 'n' => count($cands)];
                    }
                }
            }
            // un titulo exacto en un anio concreto basta: buscar sin anio solo traeria homonimos
            if ($y > 0 && array_filter($cands, fn ($c) => $c['exact'])) {
                break;
            }
        }
        $cands = array_values($cands);
        if ($exact = array_values(array_filter($cands, fn ($c) => $c['exact']))) {
            $cands = $exact;
        }
        usort($cands, fn ($a, $b) => [$a['type'] !== $type, $a['n']] <=> [$b['type'] !== $type, $b['n']]);
        return array_slice($cands, 0, 4);
    }

    /**
     * Mejor coincidencia: titulo igual (normalizado) al latino, al de Espana o al original; entre
     * varios iguales ("Fury" de 1936, 2014...) el de anio mas cercano a $near. Si no hay ninguno
     * igual, el primer resultado (marcado como no exacto).
     */
    protected function search(string $key, string $type, string $q, int $year, int $near = 0): ?array
    {
        $want = $this->bare($q);
        $first = null;
        foreach (['es-MX', 'es-ES'] as $lang) {
            $params = ['api_key' => $key, 'query' => $q, 'language' => $lang, 'include_adult' => 'false'];
            if ($year > 0) {
                $params[$type === 'movie' ? 'year' : 'first_air_date_year'] = $year;
            }
            $list = $this->get("/search/{$type}", $params)['results'] ?? [];
            $best = null;
            $bestGap = PHP_INT_MAX;
            foreach ($list as $it) {
                $same = false;
                foreach (['title', 'original_title', 'name', 'original_name'] as $f) {
                    if (isset($it[$f]) && $this->bare($it[$f]) === $want) {
                        $same = true;
                    }
                }
                if (! $same) {
                    continue;
                }
                $y = (int) substr((string) ($it['release_date'] ?? $it['first_air_date'] ?? ''), 0, 4);
                $gap = $near > 0 && $y > 0 ? abs($y - $near) : 0;
                if ($gap < $bestGap) {
                    $best = $it;
                    $bestGap = $gap;
                }
            }
            if ($best) {
                return $this->hit($best, $q, true);
            }
            $first = $first ?? ($list[0] ?? null);
        }
        return $first ? $this->hit($first, $q, false) : null;
    }

    /** Titulo comparable sin el articulo inicial: "Revenant" = "The Revenant", "Muro" = "El muro". */
    protected function bare(string $s): string
    {
        $s = preg_replace('/^\s*(the|a|an|el|la|los|las|le|les|il|o|os|as)\s+/iu', '', trim($s));
        return $this->norm($s);
    }

    private function hit(array $it, string $q, bool $exact): array
    {
        return [
            'id'    => (int) $it['id'],
            'title' => $it['title'] ?? $it['name'] ?? $q,
            'orig'  => $it['original_title'] ?? $it['original_name'] ?? '',
            'exact' => $exact,
        ];
    }

    /**
     * Videos de la ficha, mejor primero. Puntua tipo (trailer > teaser > clip) +
     * idioma de la app (y su variante regional) + oficial: un teaser doblado gana
     * a un trailer en ingles; un clip doblado, no. En espanol manda el latino: un
     * teaser latino gana a un trailer de Espana, y Espana a uno en ingles.
     */
    private function videos(string $key, string $type, int $id, string $lang): array
    {
        [$region, $alt] = match ($lang) {
            'es' => ['MX', 'ES'],
            'pt' => ['BR', 'PT'],
            default => ['US', 'GB'],
        };
        $list = $this->get("/{$type}/{$id}/videos", [
            'api_key' => $key,
            'include_video_language' => $lang === 'en' ? 'en' : "{$lang},en",
        ])['results'] ?? [];
        if (! $list) {
            // Mejor un trailer en otro idioma que ninguno.
            $list = $this->get("/{$type}/{$id}/videos", [
                'api_key' => $key,
                'include_video_language' => 'es,en,pt,fr,it,de,ja,ko,null',
            ])['results'] ?? [];
        }

        $out = [];
        foreach ($list as $v) {
            if (($v['site'] ?? '') !== 'YouTube' || empty($v['key'])) {
                continue;
            }
            $score = match ($v['type'] ?? '') {
                'Trailer' => 30,
                'Teaser' => 20,
                'Clip', 'Featurette' => 5,
                default => -100,
            };
            if (($v['iso_639_1'] ?? '') === $lang) {
                $score += $this->langScore($lang, (string) ($v['iso_3166_1'] ?? ''), (string) ($v['name'] ?? ''), $region, $alt);
            }
            if (! empty($v['official'])) {
                $score += 5;
            }
            if ($score >= 5) {
                $out[] = [
                    'key'   => $v['key'],
                    'name'  => $v['name'] ?? '',
                    'lang'  => ($v['iso_639_1'] ?? '') . '-' . ($v['iso_3166_1'] ?? ''),
                    's'     => $score,
                ];
            }
        }
        usort($out, fn ($a, $b) => $b['s'] <=> $a['s']);
        return array_slice($out, 0, 8);
    }

    /** Puntos por idioma: en espanol, latino (cualquier pais menos ES) 25-26 y Espana 10. */
    private function langScore(string $lang, string $country, string $name, string $region, string $alt): int
    {
        if ($lang !== 'es') {
            return match ($country) { $region => 12, $alt => 11, default => 10 };
        }
        $n = $this->norm($name);
        if (preg_match('/latino|latinoamerica/', $n)) {
            return 26;
        }
        if ($country === 'ES' || preg_match('/castellano|espana/', $n)) {
            return 10;
        }
        return $country === 'MX' ? 26 : 25;
    }

    /**
     * Ultimo recurso: la busqueda de YouTube. Solo acepta videos cortos cuyo titulo
     * contenga el nombre buscado y la palabra trailer/teaser/avance/promo.
     */
    private function youtubeSearch(string $title, string $orig, int $year, string $lang, bool $latino = false): array
    {
        if ($title === '') {
            return [];
        }
        $word = $lang === 'es' ? 'tráiler' : 'trailer';
        $hl = ['es' => 'es-419', 'pt' => 'pt-BR', 'en' => 'en'][$lang];
        try {
            $html = Http::timeout(8)
                ->withHeaders(['User-Agent' => self::UA, 'Accept-Language' => $hl])
                ->get('https://www.youtube.com/results', [
                    'search_query' => trim("{$title} {$word}" . ($latino ? ' latino' : '')),
                    'hl' => $hl,
                ])->body();
        } catch (\Throwable $e) {
            return [];
        }

        $names = array_filter([$this->norm($title), $this->norm($orig)]);
        $out = [];
        if (! preg_match_all('/"videoRenderer":\{"videoId":"([A-Za-z0-9_-]{11})"(.{0,4000}?)"lengthText"/s', $html, $m, PREG_SET_ORDER)) {
            return [];
        }
        foreach (array_slice($m, 0, 12) as $hit) {
            $vid = $hit[1];
            if (! preg_match('/"title":\{"runs":\[\{"text":"((?:[^"\\\\]|\\\\.)*)"/', $hit[2], $t)) {
                continue;
            }
            $name = json_decode('"' . $t[1] . '"') ?? $t[1];
            $n = $this->norm($name);
            $matchesTitle = false;
            foreach ($names as $want) {
                if ($want !== '' && str_contains($n, $want)) {
                    $matchesTitle = true;
                }
            }
            if (! $matchesTitle || ! preg_match('/trailer|teaser|avance|promo|adelanto/', $n) || ($latino && (! preg_match('/latino|doblad/', $n) || str_contains($n, 'subtitulad')))) {
                continue;
            }
            // La duracion va justo despues ("lengthText":{... "simpleText":"2:31"}); >6 min no es un trailer.
            $rest = substr($html, strpos($html, $hit[0]) + strlen($hit[0]), 400);
            // en YouTube cualquiera sube un video con el nombre: si se sabe el anio, tiene que estar en el titulo
            if ($year > 0 && ! preg_match('/\b(' . ($year - 1) . '|' . $year . '|' . ($year + 1) . ')\b/', $name)) {
                continue;
            }
            if ($this->otherFilm($name, [$title, $orig], $hit[2] . $rest, $year)) {
                continue;
            }
            if (preg_match('/"simpleText":"(\d+):(\d{2})"/', $rest, $d) && ((int) $d[1] * 60 + (int) $d[2]) > 360) {
                continue;
            }
            if ($this->embeddable($vid)) {
                $out[] = ['key' => $vid, 'name' => $name, 'lang' => $latino ? 'es-419' : $lang];
            }
            if (count($out) >= self::MAX_KEYS) {
                break;
            }
        }
        return $out;
    }

    /** Palabras de todos los nombres de la ficha: los de la app, el latino, el original y el de Espana. */
    private function filmNames(string $key, array $c, array $queries): array
    {
        $names = array_merge($queries, [$c['title'] ?? '', $c['orig'] ?? '']);
        $es = $this->get("/{$c['type']}/{$c['id']}", ['api_key' => $key, 'language' => 'es-ES', 'append_to_response' => 'credits']);
        if ($es) {
            $names[] = $es['title'] ?? $es['name'] ?? '';
            // los trailers en ingles suelen llevar los actores: "The Wall Trailer (2017) John Cena"
            foreach (array_slice($es['credits']['cast'] ?? [], 0, 10) as $p) {
                $names[] = $p['name'] ?? '';
            }
            foreach ($es['credits']['crew'] ?? [] as $p) {
                if (($p['job'] ?? '') === 'Director') {
                    $names[] = $p['name'] ?? '';
                }
            }
        }
        $words = [];
        foreach ($names as $n) {
            foreach ($this->words($n) as $w) {
                $words[$w] = true;
            }
        }
        return $words;
    }

    /**
     * El nombre del video habla de esta pelicula: quitando las palabras de relleno ("Trailer
     * Oficial Espanol HD", estudios...), lo que queda tiene que coincidir con algun nombre.
     * Un nombre sin nada propio ("Final Trailer") vale.
     */
    private function aboutFilm(string $video, array $names): bool
    {
        static $filler = ['trailer', 'trailers', 'teaser', 'oficial', 'official', 'final', 'nuevo', 'new',
            'segundo', 'second', 'primer', 'first', 'tercer', 'third', 'espanol', 'espana', 'castellano',
            'latino', 'latinoamerica', 'doblado', 'doblada', 'subtitulado', 'subtitulada', 'sub', 'subs', 'vose',
            'vos', 'audio', 'ingles', 'english', 'spanish', 'the', 'and', 'del', 'las', 'los', 'por', 'para', 'con',
            'una', 'uno', 'cines', 'cine', 'estreno', 'pelicula', 'movie', 'film', 'version', 'sony', 'pictures',
            'disney', 'pixar', 'warner', 'bros', 'universal', 'paramount', 'netflix', 'marvel', 'studios', 'hbo',
            'max', 'prime', 'video', 'amazon', 'clip', 'avance', 'adelanto', 'promo', 'spot', 'temporada',
            'season', 'parte', 'part', 'serie', 'series', 'oficiales', 'exclusivo', 'exclusive', 'full',
            'mexico', 'argentina', 'colombia', 'chile', 'peru', 'internacional', 'international', 'red', 'band',
            'imax', 'dolby', 'vision', 'tv', 'hd', 'uhd', 'ya', 'now', 'hoy', 'solo', 'only', 'cinemas', 'theaters', 'movieclips', 'fandango', 'filmisnow', 'kinocheck', 'hd', 'rated', 'pg', 'starring', 'us', 'uk', 'usa', 'intl', 'eu', 'vo', 'ver', 'trl', 'tlr', 'restricted', 'green'];
        $own = array_diff($this->words($video), $filler);
        $own = array_filter($own, fn ($w) => ! ctype_digit($w));
        if (! $own) {
            return true;
        }
        // mas de la mitad: "Corazones de acero" no vale para "T-34 Heroes de acero"
        $hits = 0;
        foreach ($own as $w) {
            if (isset($names[$w])) {
                $hits++;
            }
        }
        return $hits * 2 > count($own);
    }

    private function words(string $s): array
    {
        $out = [];
        foreach (preg_split('/[^a-z0-9]+/', strtolower(Str::ascii($s))) as $w) {
            if (strlen($w) >= 2 && ! in_array($w, ['de', 'la', 'el', 'en', 'of', 'to', 'in', 'a', 'y', 'hd', '4k'], true)) {
                $out[] = $w;
            }
        }
        return $out;
    }

    /**
     * El video de la busqueda es de OTRA pelicula aunque lleve el nombre:
     * - secuela: "Moana 2" / "Moana II" buscando "Moana" (salvo que el titulo buscado ya lleve numero);
     * - otro anio en el titulo ("Official Trailer (2017)" para la de 2026);
     * - subido anios antes del estreno (el trailer de la version anterior, un remake).
     */
    private function otherFilm(string $name, array $wanted, string $chunk, int $year): bool
    {
        $s = strtolower(Str::ascii($name));
        foreach (array_filter($wanted) as $w) {
            $w = strtolower(Str::ascii(trim($w)));
            if ($w === '' || preg_match('/(\d|\bii|\biii|\biv)$/', $w)) {
                continue;
            }
            if (preg_match('/' . preg_quote($w, '/') . '\s*[:\-]?\s*(\d{1,2}|ii|iii|iv)\b/', $s)) {
                return true;
            }
        }
        if ($year <= 0) {
            return false;
        }
        if (preg_match_all('/\b(19\d\d|20\d\d)\b/', $s, $ys)) {
            $near = false;
            foreach ($ys[1] as $y) {
                if (abs((int) $y - $year) <= 1) {
                    $near = true;
                }
            }
            if (! $near) {
                return true;
            }
        }
        if (preg_match('/"publishedTimeText":\{"simpleText":"([^"]*)"/', $chunk, $pt)
            && preg_match('/(\d+)\s*(a(?:ñ|n)os?|years?)\b/iu', $pt[1], $ago)
            && (int) date('Y') - (int) $ago[1] < $year - 1) {
            return true;
        }
        return false;
    }

    /** oEmbed: 200 = se puede ver fuera de YouTube; 401/403 = inserción desactivada; 404 = borrado. */
    private function embeddable(string $vid): bool
    {
        $ck = 'yt_embed_ok_' . $vid;
        if (($hit = Cache::get($ck)) !== null) {
            return (bool) $hit;
        }
        try {
            $status = Http::timeout(5)->withHeaders(['User-Agent' => self::UA])
                ->get('https://www.youtube.com/oembed', [
                    'url' => 'https://www.youtube.com/watch?v=' . $vid,
                    'format' => 'json',
                ])->status();
        } catch (\Throwable $e) {
            return true;    // sin red no se descarta ni se cachea: la app tiene reservas
        }
        if ($status >= 500 || $status === 429) {
            return true;    // fallo de YouTube, no del video
        }
        Cache::put($ck, $status === 200 ? 1 : 0, 604800);
        return $status === 200;
    }

    protected function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($s)));
    }

    protected function get(string $path, array $params): ?array
    {
        try {
            $res = Http::timeout(8)->get(self::BASE . $path, $params);
            return $res->successful() ? $res->json() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

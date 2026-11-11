<?php

namespace App\Http\Controllers\AppTv;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Datos de TMDB para la ficha del movil: portada y fondo en HD, generos en espanol,
 * duracion o temporadas, direccion y reparto con foto.
 *
 * GET /api/app-tv/ficha?title=&orig=&year=&type=movie|tv&cast=Nombre|Nombre
 *
 * Solo responde ok si la coincidencia es segura: titulo exacto y, si la app manda
 * reparto, al menos un actor en comun; sin reparto, el anio tiene que cuadrar.
 * Asi una portada equivocada nunca sustituye a la del catalogo.
 *
 * Aciertos cacheados 7 dias; fallos 1 dia.
 */
class FichaController extends TrailerController
{
    private const IMG = 'https://image.tmdb.org/t/p/';
    private const NO_NAMES = ['unknown', 'desconocido', 'desconhecido', 'na', 'none'];

    public function __invoke(Request $r)
    {
        $title = mb_substr(trim((string) $r->query('title', '')), 0, 120);
        $orig  = mb_substr(trim((string) $r->query('orig', '')), 0, 120);
        $year  = (int) substr(preg_replace('/\D/', '', (string) $r->query('year', '')), 0, 4);
        $type  = in_array($r->query('type'), ['movie', 'tv'], true) ? $r->query('type') : 'auto';
        $cast = [];
        foreach (explode('|', mb_substr((string) $r->query('cast', ''), 0, 600)) as $n) {
            $n = $this->norm($n);
            if ($n !== '' && ! in_array($n, self::NO_NAMES, true) && count($cast) < 12) {
                $cast[] = $n;
            }
        }

        // id de TMDB ya identificado (p. ej. por /trailer con el titulo exacto): esa ficha tal cual
        $tmdbId = max(0, (int) $r->query('tmdb', 0));
        if ($tmdbId > 0 && $type !== 'auto') {
            $ck = 'tv_ficha_id_v1_' . $type . $tmdbId;
            if (($hit = Cache::get($ck)) !== null) {
                return response()->json($hit);
            }
            $data = ['ok' => false, 'reason' => 'not_found'];
            if ($key = config('services.tmdb.key')) {
                $d = $this->get("/{$type}/{$tmdbId}", [
                    'api_key' => $key,
                    'language' => 'es-MX',
                    'append_to_response' => 'credits,images',
                    'include_image_language' => 'en,null',
                ]);
                if ($d && isset($d['id'])) {
                    $data = $this->card($type, $d, $d['credits']['cast'] ?? []);
                }
            }
            Cache::put($ck, $data, $data['ok'] ? 604800 : 3600);
            return response()->json($data);
        }

        if ($title === '' && $orig === '') {
            return response()->json(['ok' => false, 'reason' => 'no_title']);
        }

        $ck = 'tv_ficha_v4_' . md5(implode('|', [$title, $orig, $year, $type, implode(',', $cast)]));
        if (($hit = Cache::get($ck)) !== null) {
            return response()->json($hit);
        }
        $data = $this->lookup($title, $orig, $year, $type, $cast);
        Cache::put($ck, $data, $data['ok'] ? 604800 : 86400);
        return response()->json($data);
    }

    private function lookup(string $title, string $orig, int $year, string $type, array $cast): array
    {
        if (! ($key = config('services.tmdb.key'))) {
            return ['ok' => false, 'reason' => 'no_key'];
        }
        $queries = array_values(array_unique(array_filter([$this->clean($orig), $this->clean($title)])));
        if (! $queries) {
            return ['ok' => false, 'reason' => 'no_title'];
        }

        foreach ($this->candidates($key, $queries, $year, $type) as $c) {
            if (! $c['exact']) {
                continue;
            }
            $d = $this->get("/{$c['type']}/{$c['id']}", [
                'api_key' => $key,
                'language' => 'es-MX',
                'append_to_response' => 'credits,images',
                'include_image_language' => 'en,null',
            ]);
            if (! $d) {
                continue;
            }
            $people = $d['credits']['cast'] ?? [];
            if ($cast) {
                $theirs = array_map(fn ($p) => $this->norm($p['name'] ?? ''), array_slice($people, 0, 60));
                if (! array_intersect($cast, $theirs)) {
                    continue;
                }
            } else {
                $y = (int) substr((string) ($d['release_date'] ?? $d['first_air_date'] ?? ''), 0, 4);
                if ($year <= 0 || $y <= 0) {
                    continue;
                }
                // una serie puede llevar el anio de la temporada (nunca antes del estreno, ni decadas despues)
                if ($c['type'] === 'movie' ? abs($y - $year) > 1 : ($y > $year + 1 || $year - $y > 20)) {
                    continue;
                }
            }
            return $this->card($c['type'], $d, $people);
        }
        return ['ok' => false, 'reason' => 'not_found'];
    }

    private function card(string $type, array $d, array $people): array
    {
        $directors = $type === 'movie'
            ? array_column(array_filter($d['credits']['crew'] ?? [], fn ($p) => ($p['job'] ?? '') === 'Director'), 'name')
            : array_column($d['created_by'] ?? [], 'name');

        $cast = [];
        foreach (array_slice($people, 0, 15) as $p) {
            if (empty($p['name'])) {
                continue;
            }
            $cast[] = [
                'name'  => $p['name'],
                'role'  => (string) ($p['character'] ?? ''),
                'photo' => ! empty($p['profile_path']) ? self::IMG . 'w185' . $p['profile_path'] : null,
            ];
        }

        return [
            'ok'       => true,
            'type'     => $type,
            'tmdb'     => (int) $d['id'],
            'poster'   => ($pp = $this->posterEn($d)) ? self::IMG . 'w780' . $pp : null,
            // la original (unos 2000x3000) para la portada a toda pantalla del movil
            'poster_hd' => $pp ? self::IMG . 'original' . $pp : null,
            'backdrop' => ! empty($d['backdrop_path']) ? self::IMG . 'w1280' . $d['backdrop_path'] : null,
            'genres'   => array_slice(array_column($d['genres'] ?? [], 'name'), 0, 3),
            'runtime'  => (int) ($d['runtime'] ?? 0),
            'seasons'  => (int) ($d['number_of_seasons'] ?? 0),
            'director' => array_values(array_slice($directors, 0, 2)),
            'cast'     => $cast,
        ];
    }

    /** Ruta de la portada original en ingles (o sin texto); la traducida solo si no hay otra. */
    private function posterEn(array $d): ?string
    {
        $all = $d['images']['posters'] ?? [];
        foreach (['en', null] as $want) {
            foreach ($all as $p) {
                if (($p['iso_639_1'] ?? null) === $want && ! empty($p['file_path'])) {
                    return $p['file_path'];
                }
            }
        }
        return ! empty($d['poster_path']) ? $d['poster_path'] : null;
    }
}

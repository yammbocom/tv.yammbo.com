<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Podcasts para la app de TV.
 *
 *   GET /api/app-tv/podcasts/home             -> { rows: [{title, items:[{id,title,author,cover}]}] }
 *   GET /api/app-tv/podcasts/search?q=        -> { items: [...] }
 *   GET /api/app-tv/podcasts/show/{browseId}  -> { podcast: {id,title,author,description,cover,
 *                                                  episodes:[{id,title,date,duration,description,thumb}]} }
 *
 * Los datos salen del cliente de YouTube Music de music.yammbo.com (mismo VPS) por su
 * puente interno /internal/tv/podcasts/*, que solo acepta peticiones locales. Aqui se
 * adaptan para la TV: textos en espanol, miniaturas que no caducan y descripciones cortas.
 * El video se reproduce en la TV directo desde YouTube (cero ancho de banda del VPS).
 */
class PodcastController extends Controller
{
    private const HOME_KEY = 'tv_podcasts_home_v3';
    private const HOME_FRESH = 3600;   // pasado esto se refresca en segundo plano

    public function home()
    {
        $hit = Cache::get(self::HOME_KEY);
        if ($hit && ! empty($hit['rows'])) {
            if (time() - ($hit['at'] ?? 0) > self::HOME_FRESH && Cache::add(self::HOME_KEY . ':busy', 1, 300)) {
                // Se sirve lo guardado y se refresca despues de responder (la primera
                // construccion tarda ~30 s y la app no espera tanto).
                app()->terminating(function () {
                    $this->refreshHome();
                    Cache::forget(self::HOME_KEY . ':busy');
                });
            }
            return response()->json(['rows' => $hit['rows']]);
        }
        // En frio (deploy, cache:clear): una sola construccion a la vez; el resto contesta
        // enseguida para que la app ofrezca reintentar en vez de ocupar mas workers.
        if (! Cache::add(self::HOME_KEY . ':busy', 1, 300)) {
            return response()->json(['rows' => [], 'building' => true], 503)->header('Retry-After', '15');
        }
        try {
            $rows = $this->refreshHome();
        } finally {
            Cache::forget(self::HOME_KEY . ':busy');
        }
        return response()->json(['rows' => $rows], $rows ? 200 : 502);
    }

    public function search(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 80);
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }
        $key = 'tv_podcasts_search_v3_' . md5(mb_strtolower($q));
        if (($hit = Cache::get($key)) !== null) {
            return response()->json(['items' => $hit]);
        }
        $data = $this->bridge('search', ['q' => $q, 'fast' => 1], 25);
        if ($data === null) {
            return response()->json(['items' => []], 502);
        }
        $items = array_values(array_filter(array_map([$this, 'show_item'], $data['items'] ?? [])));
        // portadas firmadas (caducan en horas): se guarda poco para que no lleguen a caducar en cache
        $ttl = ! $items ? 1800 : (! empty($data['expiring']) ? 2700 : 21600);
        Cache::put($key, $items, $ttl);
        return response()->json(['items' => $items]);
    }

    public function show(string $browseId)
    {
        $key = 'tv_podcasts_show_v7_' . $browseId;
        if (($hit = Cache::get($key)) !== null) {
            return $hit === false
                ? response()->json(['message' => 'not found'], 404)
                : response()->json(['podcast' => $hit]);
        }
        // en espanol: titulos originales (en ingles YouTube pone las traducciones del canal)
        $data = $this->bridge('show/' . $browseId, ['hl' => 'es'], 25, $status);
        if ($data === null || empty($data['podcast'])) {
            if ($status === 404) {
                Cache::put($key, false, 900);
                return response()->json(['message' => 'not found'], 404);
            }
            return response()->json(['message' => 'unavailable'], 502);
        }
        $p = $data['podcast'];
        $episodes = [];
        foreach ($p['episodes'] ?? [] as $e) {
            $vid = (string) ($e['videoId'] ?? '');
            if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $vid)) {
                continue;
            }
            $episodes[] = [
                'id'          => $vid,
                'title'       => $this->plain((string) ($e['title'] ?? '')),
                'date'        => $this->ago((string) ($e['date'] ?? '')),
                'duration'    => $this->duration((string) ($e['duration'] ?? '')),
                'description' => $this->shortText((string) ($e['description'] ?? ''), 360),
                'thumb'       => "https://i.ytimg.com/vi/{$vid}/hqdefault.jpg",
            ];
        }
        $out = [
            'id'          => $p['browseId'] ?? $browseId,
            'title'       => $this->plain((string) ($p['title'] ?? '')),
            'author'      => $this->plain((string) preg_replace('/^p[oó]dcast\s*[•·]\s*/iu', '', (string) ($p['author'] ?? ''))),
            'description' => $this->shortText((string) ($p['description'] ?? ''), 600),
            'cover'       => $this->cover($p['thumbnail'] ?? null, $episodes),
            'episodes'    => $episodes,
        ];
        Cache::put($key, $out, 3600);
        return response()->json(['podcast' => $out]);
    }

    // ------------------------------------------------------------------

    private function refreshHome(): array
    {
        $data = $this->bridge('home', [], 150);
        $rows = [];
        foreach ($data['rows'] ?? [] as $row) {
            $items = array_values(array_filter(array_map([$this, 'show_item'], $row['items'] ?? [])));
            if ($items) {
                $rows[] = ['title' => (string) ($row['title'] ?? ''), 'items' => $items];
            }
        }
        if ($rows) {
            Cache::forever(self::HOME_KEY, ['at' => time(), 'rows' => $rows]);
        }
        return $rows;
    }

    /** Ficha minima de un podcast para las filas y la busqueda. */
    private function show_item($it): ?array
    {
        $id = (string) ($it['browseId'] ?? '');
        if (! preg_match('/^MPSP[\w\-]{8,80}$/', $id)) {
            return null;
        }
        return [
            'id'     => $id,
            'title'  => $this->plain((string) ($it['title'] ?? '')),
            'author' => $this->plain((string) ($it['author'] ?? '')),
            'cover'  => (string) ($it['thumbnail'] ?? ''),
        ];
    }

    /** Sin emojis ni simbolos decorativos (la app no los muestra). */
    private function plain(string $s): string
    {
        $s = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{3010}\x{3011}\x{FE0F}\x{200D}]/u', '', $s);
        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /** Llama al puente local de music.yammbo.com (sin salir del servidor ni pasar por Cloudflare). */
    private function bridge(string $path, array $query, int $timeout, ?int &$status = null): ?array
    {
        try {
            $res = Http::timeout($timeout)
                ->withoutVerifying()
                ->withOptions(['curl' => [CURLOPT_RESOLVE => ['music.yammbo.com:443:127.0.0.1']]])
                ->acceptJson()
                ->get('https://music.yammbo.com/internal/tv/podcasts/' . $path, $query);
            $status = $res->status();
            return $res->successful() ? $res->json() : null;
        } catch (\Throwable $e) {
            $status = 0;
            return null;
        }
    }

    /** Portada fija: la del podcast si no es un collage firmado (caduca); si no, la del primer episodio. */
    private function cover(?string $thumb, array $episodes): string
    {
        $signed = $thumb && (str_contains($thumb, '/pl_c/') || str_contains($thumb, 'sqp='));
        if ($thumb && ! $signed) {
            return $thumb;
        }
        return $episodes[0]['thumb'] ?? (string) $thumb;
    }

    /** "5.6K views • 1d ago" / "1 M de visualizaciones • hace 5 d" -> "Hace 1 día". */
    private function ago(string $s): string
    {
        $parts = preg_split('/\s*[•·]\s*/u', $s);
        $last = trim((string) end($parts));
        if (preg_match('/^hace\s+(\d+)\s*(s|seg|segundos?|min|minutos?|h|horas?|d|días?|sem|semanas?|mes|meses|a|años?)\.?$/iu', $last, $m)) {
            $n = (int) $m[1];
            $u = mb_strtolower($m[2]);
            [$one, $many] = match (true) {
                str_starts_with($u, 'sem') => ['semana', 'semanas'],
                str_starts_with($u, 's')   => ['segundo', 'segundos'],
                str_starts_with($u, 'min') => ['minuto', 'minutos'],
                str_starts_with($u, 'h')   => ['hora', 'horas'],
                str_starts_with($u, 'd')   => ['día', 'días'],
                str_starts_with($u, 'mes') => ['mes', 'meses'],
                default                    => ['año', 'años'],
            };
            return 'Hace ' . $n . ' ' . ($n === 1 ? $one : $many);
        }
        if (! preg_match('/(\d+)\s*(s|sec|second|m|min|minute|h|hr|hour|d|day|w|wk|week|mo|month|y|yr|year)s?\s+ago/i', $last, $m)) {
            if (preg_match('/views?|visualizaci/iu', $last)) {
                return '';
            }
            return $this->date($last);
        }
        $n = (int) $m[1];
        $u = strtolower($m[2]);
        [$one, $many] = match (true) {
            in_array($u, ['s', 'sec', 'second'])  => ['segundo', 'segundos'],
            in_array($u, ['m', 'min', 'minute'])  => ['minuto', 'minutos'],
            in_array($u, ['h', 'hr', 'hour'])     => ['hora', 'horas'],
            in_array($u, ['d', 'day'])            => ['día', 'días'],
            in_array($u, ['w', 'wk', 'week'])     => ['semana', 'semanas'],
            in_array($u, ['mo', 'month'])         => ['mes', 'meses'],
            default                               => ['año', 'años'],
        };
        return 'Hace ' . $n . ' ' . ($n === 1 ? $one : $many);
    }

    /** "Sep 18" -> "18 sep."; "Sep 18, 2024" -> "18 sep. 2024". Lo que no reconoce, igual. */
    private function date(string $s): string
    {
        $months = ['jan' => 'ene.', 'feb' => 'feb.', 'mar' => 'mar.', 'apr' => 'abr.', 'may' => 'may.', 'jun' => 'jun.',
            'jul' => 'jul.', 'aug' => 'ago.', 'sep' => 'sep.', 'oct' => 'oct.', 'nov' => 'nov.', 'dec' => 'dic.'];
        // en espanol ya viene "14 sept" o "1 jul 2024": solo se unifica la abreviatura
        if (preg_match('/^(\d{1,2})\s+(?:de\s+)?(\pL{3})\pL*\.?(?:\s+(?:de\s+)?(\d{4}))?$/u', trim($s), $m)) {
            $es = ['ene' => 'ene.', 'feb' => 'feb.', 'mar' => 'mar.', 'abr' => 'abr.', 'may' => 'may.', 'jun' => 'jun.',
                'jul' => 'jul.', 'ago' => 'ago.', 'sep' => 'sep.', 'oct' => 'oct.', 'nov' => 'nov.', 'dic' => 'dic.'];
            $mon = $es[mb_strtolower($m[2])] ?? null;
            if ($mon !== null) {
                return trim((int) $m[1] . ' ' . $mon . ' ' . ($m[3] ?? ''));
            }
        }
        if (preg_match('/^([A-Za-z]{3})[a-z]*\.?\s+(\d{1,2})(?:,\s*(\d{4}))?$/', trim($s), $m)) {
            $mon = $months[strtolower($m[1])] ?? null;
            if ($mon !== null) {
                return trim((int) $m[2] . ' ' . $mon . ' ' . ($m[3] ?? ''));
            }
        }
        return $s;
    }

    /** "1 hr 9 min" -> "1 h 9 min"; "45 min" igual; "1:02:03" -> "1 h 2 min". */
    private function duration(string $s): string
    {
        $s = trim(preg_replace('/^[\s•·]+|[\s•·]+$/u', '', $s));
        if (preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{2})$/', $s, $m)) {
            $h = (int) $m[1];
            $min = (int) $m[2];
            return $h ? "{$h} h {$min} min" : "{$min} min";
        }
        $s = preg_replace(['/\bhrs?\b/i', '/\bhours?\b/i', '/\bmins?\b/i', '/\bminutes?\b/i', '/\bsecs?\b/i', '/\bseconds?\b/i', '/\bleft\b/i'],
            ['h', 'h', 'min', 'min', 's', 's', ''], $s);
        return trim($s);
    }

    /** Descripcion sin enlaces, menciones ni hashtags, cortada en una frase. */
    private function shortText(string $s, int $max): string
    {
        $s = preg_replace('~https?://\S+~u', '', $s);
        $s = preg_replace('/(^|\s)[@#][\w\-]+/u', ' ', $s);
        $s = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u', '', $s);
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        if (mb_strlen($s) <= $max) {
            return $s;
        }
        $cut = mb_substr($s, 0, $max);
        $dot = max(mb_strrpos($cut, '. ') ?: 0, mb_strrpos($cut, '! ') ?: 0, mb_strrpos($cut, '? ') ?: 0);
        return $dot > $max * 0.5 ? mb_substr($cut, 0, $dot + 1) : rtrim($cut) . '…';
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Datos para la vista previa de un enlace compartido (og:title/description/image).
 *
 * TMDB primero, en el idioma pedido (el público es hispano, así que el texto sale
 * en español); Cinemeta como respaldo sin clave. Los éxitos se guardan 7 días y
 * los fallos 10 minutos, para no machacar al proveedor si está caído ni dejar
 * una ficha sin portada durante una semana. Timeouts cortos: quien espera es el
 * bot de WhatsApp o Telegram, y si tarda demasiado se rinde y no pinta nada.
 */
class SharePreview
{
    private const TMDB = 'https://api.themoviedb.org/3';
    private const TMDB_IMG = 'https://image.tmdb.org/t/p';
    private const CINEMETA = 'https://v3-cinemeta.strem.io/meta';
    private const TTL_OK = 604800;
    private const TTL_MISS = 600;

    /**
     * @return array{title:string,description:?string,image:?string,backdrop:?string,year:?string}|null
     */
    public function for(string $type, string $id, string $locale = 'es'): ?array
    {
        // Solo ids que sabemos resolver. Cualquier otra cosa ni se cachea ni sale
        // a la red: la ruta es pública y la caché es de ficheros.
        if (! preg_match('/^(tt\d{1,10}|tmdb:\d{1,10}|yammbo:(movie|tv):\d{1,10})$/D', $id)) {
            return null;
        }
        // El prefijo manda sobre el tipo de la URL (yammbo:tv:N es siempre serie).
        if (str_starts_with($id, 'yammbo:tv:')) {
            $type = 'series';
        } elseif (str_starts_with($id, 'yammbo:movie:')) {
            $type = 'movie';
        }
        $type = $type === 'series' ? 'series' : 'movie';
        $lang = str_starts_with($locale, 'en') ? 'en-US' : 'es-ES';
        $key = "share-preview:v1:{$type}:{$id}:{$lang}";

        $hit = Cache::get($key);
        if ($hit !== null) {
            return $hit === false ? null : $hit;
        }

        $data = null;
        try {
            $data = $this->fromTmdb($type, $id, $lang);
            if (! $data || ! $data['description']) {
                $cinemeta = $this->fromCinemeta($type, $id);
                if (! $data) {
                    $data = $cinemeta;
                } elseif ($cinemeta) {
                    $data['description'] = $cinemeta['description'];
                }
            }
            if ($data) {
                $data['image'] = $this->safeImage($data['image']);
                $data['backdrop'] = $this->safeImage($data['backdrop']);
            }
        } catch (\Throwable $e) {
            Log::warning('share-preview: lookup failed', ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);
        }

        Cache::put($key, $data ?: false, $data ? self::TTL_OK : self::TTL_MISS);

        return $data;
    }

    private function fromTmdb(string $type, string $id, string $lang): ?array
    {
        $apiKey = config('services.tmdb.key');
        if (! $apiKey) {
            return null;
        }

        if (preg_match('/^tt\d+$/', $id)) {
            $res = Http::timeout(4)->get(self::TMDB."/find/{$id}", [
                'api_key' => $apiKey, 'external_source' => 'imdb_id', 'language' => $lang,
            ]);
            if (! $res->successful()) {
                return null;
            }
            $bucket = $type === 'series' ? 'tv_results' : 'movie_results';
            $item = $res->json($bucket.'.0') ?? $res->json(($type === 'series' ? 'movie_results' : 'tv_results').'.0');
        } elseif (preg_match('/^(?:tmdb|yammbo:(?:movie|tv)):(\d+)$/', $id, $m)) {
            $res = Http::timeout(4)->get(self::TMDB.'/'.($type === 'series' ? 'tv' : 'movie')."/{$m[1]}", [
                'api_key' => $apiKey, 'language' => $lang,
            ]);
            $item = $res->successful() ? $res->json() : null;
        } else {
            return null;
        }

        if (! is_array($item)) {
            return null;
        }

        $title = $item['title'] ?? $item['name'] ?? null;
        if (! $title) {
            return null;
        }
        $date = $item['release_date'] ?? $item['first_air_date'] ?? null;

        return [
            'title' => $title,
            'description' => trim((string) ($item['overview'] ?? '')) ?: null,
            'image' => ! empty($item['poster_path']) ? self::TMDB_IMG.'/w500'.$item['poster_path'] : null,
            'backdrop' => ! empty($item['backdrop_path']) ? self::TMDB_IMG.'/w1280'.$item['backdrop_path'] : null,
            'year' => $date ? substr($date, 0, 4) : null,
        ];
    }

    /**
     * Solo URLs https de los CDN de imágenes esperados y sin caracteres que
     * puedan romper el url('...') del style en la vista.
     */
    private function safeImage(?string $url): ?string
    {
        if (! $url || ! preg_match('~^https://(image\.tmdb\.org|images\.metahub\.space)/[^\s\'"()\\<>]+$~D', $url)) {
            return null;
        }

        return $url;
    }

    private function fromCinemeta(string $type, string $id): ?array
    {
        if (! preg_match('/^tt\d+$/', $id)) {
            return null;
        }
        $res = Http::timeout(4)->get(self::CINEMETA."/{$type}/{$id}.json");
        $meta = $res->successful() ? $res->json('meta') : null;
        if (! is_array($meta) || empty($meta['name'])) {
            return null;
        }

        return [
            'title' => $meta['name'],
            'description' => trim((string) ($meta['description'] ?? '')) ?: null,
            'image' => isset($meta['poster']) ? str_replace('/poster/small/', '/poster/medium/', $meta['poster']) : null,
            'backdrop' => $meta['background'] ?? null,
            'year' => isset($meta['year']) ? substr((string) $meta['year'], 0, 4) : null,
        ];
    }
}

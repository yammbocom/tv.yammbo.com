<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Avatares estilo Netflix para "Mi Cuenta" de la app de TV.
 *
 * Devuelve los personajes principales de las series/peliculas en tendencia
 * (foto del actor via TMDB). Cacheado 24h para no golpear la API por cada TV.
 */
class AvatarsController extends Controller
{
    private const BASE = 'https://api.themoviedb.org/3';
    private const IMG  = 'https://image.tmdb.org/t/p/w185';
    private const TTL  = 86400; // 24h
    private const WANT = 18;    // avatares a devolver

    public function index(): JsonResponse
    {
        $avatars = Cache::remember('yambo_tv_avatars_v1', self::TTL, function () {
            return $this->build();
        });

        return response()->json(['avatars' => $avatars ?: []]);
    }

    private function build(): array
    {
        $key = config('services.tmdb.key');
        if (! $key) {
            return [];
        }

        $out = [];
        $seen = [];

        // Tendencias de la semana: series + peliculas
        foreach ([['tv', '/trending/tv/week'], ['movie', '/trending/movie/week']] as [$type, $path]) {
            $trending = $this->get($path, ['api_key' => $key, 'language' => 'es-ES']);
            $results = $trending['results'] ?? [];

            foreach (array_slice($results, 0, 6) as $item) {
                if (count($out) >= self::WANT) {
                    break 2;
                }
                $id = $item['id'] ?? null;
                if (! $id) {
                    continue;
                }
                $title = $item['name'] ?? $item['title'] ?? '';

                $credits = $this->get("/{$type}/{$id}/credits", ['api_key' => $key]);
                $cast = $credits['cast'] ?? [];

                // Hasta 2 personajes principales por titulo, con foto
                $taken = 0;
                foreach ($cast as $person) {
                    if ($taken >= 2 || count($out) >= self::WANT) {
                        break;
                    }
                    $profile = $person['profile_path'] ?? null;
                    if (! $profile) {
                        continue;
                    }
                    $character = trim((string) ($person['character'] ?? ''));
                    $actor = trim((string) ($person['name'] ?? ''));
                    $label = $character !== '' ? $character : $actor;
                    if ($label === '') {
                        continue;
                    }
                    $imgUrl = self::IMG . $profile;
                    if (isset($seen[$imgUrl])) {
                        continue;
                    }
                    $seen[$imgUrl] = true;

                    $out[] = [
                        'img'   => $imgUrl,
                        'name'  => $label,
                        'from'  => $title,
                    ];
                    $taken++;
                }
            }
        }

        return $out;
    }

    private function get(string $path, array $params): ?array
    {
        try {
            $res = Http::timeout(10)->get(self::BASE . $path, $params);
            if (! $res->successful()) {
                return null;
            }
            return $res->json();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

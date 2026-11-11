<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TmdbService
{
    private const BASE = 'https://api.themoviedb.org/3';
    private const IMG = 'https://image.tmdb.org/t/p';
    private const TTL = 3600;

    public function popularMovies(int $page = 1, string $language = 'en-US'): ?array
    {
        return $this->cached("tmdb:popular:movie:{$page}:{$language}", fn () => $this->get('/movie/popular', [
            'page' => $page,
            'language' => $language,
        ]));
    }

    public function popularSeries(int $page = 1, string $language = 'en-US'): ?array
    {
        return $this->cached("tmdb:popular:series:{$page}:{$language}", fn () => $this->get('/tv/popular', [
            'page' => $page,
            'language' => $language,
        ]));
    }

    public function movie(int $tmdbId, string $language = 'en-US'): ?array
    {
        return $this->cached("tmdb:movie:{$tmdbId}:{$language}", fn () => $this->get("/movie/{$tmdbId}", [
            'append_to_response' => 'credits,videos,external_ids',
            'language' => $language,
        ]));
    }

    public function series(int $tmdbId, string $language = 'en-US'): ?array
    {
        return $this->cached("tmdb:series:{$tmdbId}:{$language}", fn () => $this->get("/tv/{$tmdbId}", [
            'append_to_response' => 'credits,videos,external_ids',
            'language' => $language,
        ]));
    }

    public function discover(string $type, int $genreId, int $page = 1, string $language = 'en-US'): ?array
    {
        $media = $type === 'series' ? 'tv' : 'movie';

        return $this->cached("tmdb:discover:{$media}:{$genreId}:{$page}:{$language}", fn () => $this->get("/discover/{$media}", [
            'with_genres' => $genreId,
            'sort_by' => 'popularity.desc',
            'page' => $page,
            'language' => $language,
        ]));
    }

    public function search(string $type, string $query, string $language = 'en-US'): ?array
    {
        $media = $type === 'series' ? 'tv' : 'movie';

        return $this->cached("tmdb:search:{$media}:" . md5($query) . ":{$language}", fn () => $this->get("/search/{$media}", [
            'query' => $query,
            'language' => $language,
        ]));
    }

    public function findByImdb(string $imdbId): ?array
    {
        return $this->cached("tmdb:find:{$imdbId}", fn () => $this->get("/find/{$imdbId}", ['external_source' => 'imdb_id']));
    }

    /**
     * Varias fichas a la vez (p. ej. la fila "Todas las películas"), en paralelo
     * y reutilizando la misma caché que movie()/series(). Devuelve id => ficha.
     */
    public function moviesByIds(array $tmdbIds, string $language = 'en-US'): array
    {
        return $this->batchByIds('movie', $tmdbIds, $language);
    }

    public function seriesByIds(array $tmdbIds, string $language = 'en-US'): array
    {
        return $this->batchByIds('series', $tmdbIds, $language);
    }

    public function toStremioMeta(array $tmdb, string $type): array
    {
        $imdb = $tmdb['external_ids']['imdb_id'] ?? null;
        $id = $imdb ?: ($type === 'series' ? "yammbo:tv:{$tmdb['id']}" : "yammbo:movie:{$tmdb['id']}");
        $title = $tmdb['title'] ?? $tmdb['name'] ?? 'Untitled';
        $released = $tmdb['release_date'] ?? $tmdb['first_air_date'] ?? null;

        return [
            'id' => $id,
            'type' => $type,
            'name' => $title,
            'poster' => $tmdb['poster_path'] ? self::IMG . '/w500' . $tmdb['poster_path'] : null,
            'posterShape' => 'regular',
            'background' => $tmdb['backdrop_path'] ? self::IMG . '/original' . $tmdb['backdrop_path'] : null,
            'logo' => null,
            'description' => $tmdb['overview'] ?? null,
            'releaseInfo' => $released ? substr($released, 0, 4) : null,
            'imdbRating' => isset($tmdb['vote_average']) ? (string) round($tmdb['vote_average'], 1) : null,
            'genres' => array_column($tmdb['genres'] ?? [], 'name'),
            'runtime' => isset($tmdb['runtime']) ? $tmdb['runtime'] . ' min' : null,
        ];
    }

    private function get(string $path, array $params = []): ?array
    {
        $params['api_key'] = config('services.tmdb.key');
        $params['language'] = $params['language'] ?? 'en-US';

        $res = Http::timeout(10)->get(self::BASE . $path, $params);
        return $res->successful() ? $res->json() : null;
    }

    private function cached(string $key, \Closure $fn): ?array
    {
        return Cache::remember($key, self::TTL, $fn);
    }

    private function batchByIds(string $type, array $tmdbIds, string $language): array
    {
        $media = $type === 'series' ? 'tv' : 'movie';
        $out = [];
        $missing = [];
        foreach ($tmdbIds as $id) {
            $id = (int) $id;
            $key = "tmdb:{$type}:{$id}:{$language}";
            $cached = Cache::get($key);
            if (is_array($cached)) {
                $out[(string) $id] = $cached;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($id) => $pool->as((string) $id)
                    ->timeout(10)
                    ->get(self::BASE . "/{$media}/{$id}", [
                        'api_key' => config('services.tmdb.key'),
                        'append_to_response' => 'credits,videos,external_ids',
                        'language' => $language,
                    ]),
                $missing
            ));

            foreach ($missing as $id) {
                $res = $responses[(string) $id] ?? null;
                $data = $res instanceof \Illuminate\Http\Client\Response && $res->successful() ? $res->json() : null;
                if (is_array($data)) {
                    Cache::put("tmdb:{$type}:{$id}:{$language}", $data, self::TTL);
                    $out[(string) $id] = $data;
                }
            }
        }

        return $out;
    }
}

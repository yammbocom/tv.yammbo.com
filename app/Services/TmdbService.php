<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TmdbService
{
    private const BASE = 'https://api.themoviedb.org/3';
    private const IMG = 'https://image.tmdb.org/t/p';
    private const TTL = 3600;

    public function popularMovies(int $page = 1): ?array
    {
        return $this->cached("tmdb:popular:movie:{$page}", fn () => $this->get('/movie/popular', ['page' => $page]));
    }

    public function popularSeries(int $page = 1): ?array
    {
        return $this->cached("tmdb:popular:series:{$page}", fn () => $this->get('/tv/popular', ['page' => $page]));
    }

    public function movie(int $tmdbId): ?array
    {
        return $this->cached("tmdb:movie:{$tmdbId}", fn () => $this->get("/movie/{$tmdbId}", ['append_to_response' => 'credits,videos,external_ids']));
    }

    public function series(int $tmdbId): ?array
    {
        return $this->cached("tmdb:series:{$tmdbId}", fn () => $this->get("/tv/{$tmdbId}", ['append_to_response' => 'credits,videos,external_ids']));
    }

    public function findByImdb(string $imdbId): ?array
    {
        return $this->cached("tmdb:find:{$imdbId}", fn () => $this->get("/find/{$imdbId}", ['external_source' => 'imdb_id']));
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
}

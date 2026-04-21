<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;

class StremioAddonController extends Controller
{
    public function __construct(private TmdbService $tmdb)
    {
    }

    public function manifest(): JsonResponse
    {
        return $this->cors(response()->json([
            'id' => 'com.yammbo.tv',
            'version' => '0.2.0',
            'name' => 'Yammbo Tv',
            'description' => 'Yammbo streaming catalog, powered by TMDB.',
            'logo' => url('/favicon.ico'),
            'resources' => ['catalog', 'meta', 'stream'],
            'types' => ['movie', 'series'],
            'idPrefixes' => ['tt', 'yammbo'],
            'catalogs' => [
                [
                    'type' => 'movie',
                    'id' => 'yammbo-movies-popular',
                    'name' => 'Yammbo · Popular Movies',
                    'extra' => [['name' => 'skip', 'isRequired' => false]],
                ],
                [
                    'type' => 'series',
                    'id' => 'yammbo-series-popular',
                    'name' => 'Yammbo · Popular Series',
                    'extra' => [['name' => 'skip', 'isRequired' => false]],
                ],
            ],
            'behaviorHints' => [
                'configurable' => false,
                'configurationRequired' => false,
            ],
        ]));
    }

    public function catalog(string $type, string $id, ?string $extra = null): JsonResponse
    {
        $skip = 0;
        if ($extra && preg_match('/skip=(\d+)/', $extra, $m)) {
            $skip = (int) $m[1];
        }
        $page = max(1, (int) floor($skip / 20) + 1);

        $data = match (true) {
            $type === 'movie' && $id === 'yammbo-movies-popular' => $this->tmdb->popularMovies($page),
            $type === 'series' && $id === 'yammbo-series-popular' => $this->tmdb->popularSeries($page),
            default => null,
        };

        if (! $data || empty($data['results'])) {
            return $this->cors(response()->json(['metas' => []]));
        }

        $metas = array_map(
            fn ($item) => $this->tmdb->toStremioMeta($item, $type),
            $data['results']
        );

        return $this->cors(response()->json(['metas' => $metas]));
    }

    public function meta(string $type, string $id): JsonResponse
    {
        $tmdbData = $this->resolveTmdb($type, $id);

        if (! $tmdbData) {
            return $this->cors(response()->json(['meta' => null]));
        }

        return $this->cors(response()->json([
            'meta' => $this->tmdb->toStremioMeta($tmdbData, $type),
        ]));
    }

    public function stream(string $type, string $id): JsonResponse
    {
        return $this->cors(response()->json(['streams' => []]));
    }

    private function resolveTmdb(string $type, string $id): ?array
    {
        if (str_starts_with($id, 'tt')) {
            $find = $this->tmdb->findByImdb($id);
            $key = $type === 'series' ? 'tv_results' : 'movie_results';
            $tmdbId = $find[$key][0]['id'] ?? null;

            return $tmdbId
                ? ($type === 'series' ? $this->tmdb->series($tmdbId) : $this->tmdb->movie($tmdbId))
                : null;
        }

        if (preg_match('/^yammbo:(movie|tv):(\d+)$/', $id, $m)) {
            return $m[1] === 'tv' ? $this->tmdb->series((int) $m[2]) : $this->tmdb->movie((int) $m[2]);
        }

        return null;
    }

    private function cors(JsonResponse $res): JsonResponse
    {
        return $res
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Cache-Control', 'public, max-age=300');
    }
}

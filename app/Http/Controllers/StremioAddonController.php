<?php

namespace App\Http\Controllers;

use App\Services\ComxService;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;

/**
 * Addon Stremio único de Yammbo Tv (com.yammbo.tv).
 *
 * Reúne lo que antes eran dos addons:
 *  - los catálogos "propios" (populares de TMDB), con datos de TMDB;
 *  - el contenido de CO-MX (Cuevana / Pelispedia / Pelisjuanita), con póster y
 *    streams de embed, servido por ComxService.
 *
 * Los ids conviven por prefijo: `tt...` / `yammbo:...` (catálogos Yammbo, cuyos
 * streams los pone el addon premium) y `comx:...` (CO-MX, streams propios).
 */
class StremioAddonController extends Controller
{
    public function __construct(
        private TmdbService $tmdb,
        private ComxService $comx,
    ) {
    }

    public function manifest(): JsonResponse
    {
        $spanish = ! str_starts_with(strtolower((string) request()->header('Accept-Language', 'es')), 'en');

        $catalogs = [
            [
                'type' => 'movie',
                'id' => 'comx-popular-movies',
                'name' => 'Películas populares',
                'extra' => [['name' => 'skip', 'isRequired' => false]],
            ],
            [
                'type' => 'series',
                'id' => 'comx-popular-series',
                'name' => 'Series populares',
                'extra' => [['name' => 'skip', 'isRequired' => false]],
            ],
            [
                'type' => 'movie',
                'id' => 'comx-all-movies',
                'name' => 'Todas las películas',
                'extra' => [['name' => 'skip', 'isRequired' => false]],
            ],
            [
                'type' => 'series',
                'id' => 'comx-all-series',
                'name' => 'Todas las series',
                'extra' => [['name' => 'skip', 'isRequired' => false]],
            ],
        ];

        foreach ($this->comx->genres() as $type => $genres) {
            foreach ($genres as $genreId => $name) {
                $catalogs[] = [
                    'type' => $type,
                    'id' => "comx-{$type}-{$genreId}",
                    'name' => $name,
                    'extra' => [['name' => 'skip', 'isRequired' => false]],
                ];
            }
        }

        foreach (['movie', 'series'] as $type) {
            $catalogs[] = [
                'type' => $type,
                'id' => 'yammbo-search',
                'name' => $type === 'movie' ? 'Buscar películas' : 'Buscar series',
                'extra' => [['name' => 'search', 'isRequired' => true]],
            ];
        }

        return $this->cors(response()->json([
            'id' => 'com.yammbo.tv',
            'version' => '0.5.0',
            'name' => 'Yammbo Tv',
            'description' => $spanish
                ? 'Películas y series con póster y reproducción, servidas por Yammbo Tv.'
                : 'Movies and series with posters and playback, served by Yammbo Tv.',
            'logo' => url('/images/yambo-icon.png'),
            'resources' => ['catalog', 'meta', 'stream', 'addon_catalog'],
            'types' => ['movie', 'series'],
            'idPrefixes' => ['tt', 'yammbo', 'comx'],
            'catalogs' => $catalogs,
            'addonCatalogs' => [
                [
                    'type' => 'all',
                    'id' => 'yammbo',
                    'name' => 'Yammbo',
                ],
            ],
            'behaviorHints' => [
                'configurable' => false,
                'configurationRequired' => false,
            ],
        ], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function catalog(string $type, string $id, ?string $extra = null): JsonResponse
    {
        $skip = 0;
        $query = null;
        if ($extra) {
            if (preg_match('/skip=(\d+)/', $extra, $m)) {
                $skip = (int) $m[1];
            }
            if (preg_match('/search=([^&]+)/', $extra, $m)) {
                $query = urldecode($m[1]);
            }
        }

        $metas = $this->catalogMetas($type, $id, $skip, $query);

        return $this->cors(response()->json(
            ['metas' => $metas],
            200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    public function meta(string $type, string $id): JsonResponse
    {
        $meta = $this->resolveMeta($type, $id);

        return $this->cors(response()->json(
            ['meta' => $meta],
            200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    public function stream(string $type, string $id): JsonResponse
    {
        // Solo CO-MX trae fuentes propias; el resto las pone el addon premium.
        $streams = str_starts_with($id, 'comx:') ? $this->comx->streams($type, $id) : [];

        return $this->cors(response()->json(
            ['streams' => $streams],
            200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    /** Recomendaciones ("parecidos") del repo, para el cajón lateral del reproductor. */
    public function similar(string $type, string $id): JsonResponse
    {
        $tmdbId = preg_match('/^comx:(\d+)$/', $id, $m)
            ? (int) $m[1]
            : (preg_match('/^\d+$/', $id, $m) ? (int) $m[1] : 0);
        if ($tmdbId === 0 || ! in_array($type, ['movie', 'series'], true)) {
            return $this->cors(response()->json(['metas' => []]));
        }

        // /recommendations (lo que vio la gente que vio esto) manda; /similar solo
        // rellena y exige compartir género, porque por sí solo mezcla un thriller
        // con dibujos animados. Sin póster o con pocos votos no se ofrece.
        // Cada página se pide solo si la anterior no llegó al límite.
        $limit = 15;
        $metas = [];
        foreach ([1, 2] as $page) {
            if (count($metas) < $limit) {
                $metas = $this->similarFrom($type, $tmdbId, $this->tmdb->recommendations($type, $tmdbId, $page), null, $metas, $limit);
            }
        }

        if (count($metas) < $limit) {
            $source = $type === 'series' ? $this->tmdb->series($tmdbId, 'es-MX') : $this->tmdb->movie($tmdbId, 'es-MX');
            $genres = array_column($source['genres'] ?? [], 'id');
            // Sin géneros del original, /similar volvería a mezclar de todo: mejor menos.
            foreach ($genres === [] ? [] : [1, 2] as $page) {
                if (count($metas) < $limit) {
                    $metas = $this->similarFrom($type, $tmdbId, $this->tmdb->similar($type, $tmdbId, $page), $genres, $metas, $limit);
                }
            }
        }

        return $this->cors(response()->json(
            ['metas' => $metas],
            200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    /**
     * Añade a $metas los resultados de una página de TMDB que están en el repo, en su orden,
     * sin repetir y hasta $limit. Con $genres (ids) exige compartir al menos uno.
     */
    private function similarFrom(string $type, int $tmdbId, ?array $page, ?array $genres, array $metas, int $limit): array
    {
        $seen = array_flip(array_column($metas, 'id'));
        $byId = [];
        foreach ($page['results'] ?? [] as $item) {
            $mid = (string) ($item['id'] ?? '');
            if ($mid === '' || $mid === (string) $tmdbId || isset($seen['comx:' . $mid]) || isset($byId[$mid])
                || empty($item['poster_path']) || ($item['vote_count'] ?? 0) < 50
                || ($genres !== null && array_intersect($genres, $item['genre_ids'] ?? []) === [])) {
                continue;
            }
            $byId[$mid] = $item;
        }
        if ($byId === []) {
            return $metas;
        }

        $ids = array_map('strval', array_keys($byId));
        foreach (array_intersect($ids, $this->comx->existingIds($type, $ids)) as $mid) {
            if (count($metas) >= $limit) {
                break;
            }
            $meta = $this->comx->metaFromTmdb($byId[$mid], $type, $mid);
            // TMDB guarda a veces el título original entre corchetes ("Juego sangriento [バトル・ロワイアル]").
            $name = trim(preg_replace('/\s*\[[^\]]*\]\s*$/u', '', $meta['name']) ?? '');
            if ($name !== '') {
                $meta['name'] = $name;
            }
            $metas[] = $meta;
        }

        return $metas;
    }

    // --- catálogo -------------------------------------------------------

    private function catalogMetas(string $type, string $id, int $skip, ?string $query): array
    {
        if ($id === 'yammbo-search') {
            if ($query === null || trim($query) === '') {
                return [];
            }

            return $this->comx->searchMetas($type, trim($query));
        }

        if ($id === 'comx-popular-movies' && $type === 'movie') {
            return $this->comx->popularMetas('movie', $skip);
        }

        if ($id === 'comx-popular-series' && $type === 'series') {
            return $this->comx->popularMetas('series', $skip);
        }

        if ($id === 'comx-all-movies' && $type === 'movie') {
            return $this->comx->allMetas('movie', $skip);
        }

        if ($id === 'comx-all-series' && $type === 'series') {
            return $this->comx->allMetas('series', $skip);
        }

        $genreId = $this->comxGenreForId($id, $type);
        if ($genreId !== null) {
            return $this->comx->genreMetas($type, $genreId, $skip);
        }

        return [];
    }

    private function comxGenreForId(string $id, string $type): ?int
    {
        if (! preg_match('/^comx-(movie|series)-(\d+)$/', $id, $m)) {
            return null;
        }
        if ($m[1] !== $type) {
            return null;
        }

        return isset($this->comx->genres()[$type][(int) $m[2]]) ? (int) $m[2] : null;
    }

    // --- meta -----------------------------------------------------------

    /**
     * Los repos solo dan temporada/episodio (y a veces "Serie 3x11"): nombre,
     * sinopsis, imagen y fecha salen de TMDB, para que la lista de episodios del
     * reproductor y de la ficha diga qué es cada uno. Orden por temporada y
     * episodio (los repos se concatenan y llegaban mezclados).
     */
    private function withEpisodeDetails(int $tmdbId, array $videos): array
    {
        $seasons = $videos === [] ? [] : $this->tmdb->seasons($tmdbId, array_column($videos, 'season'));
        $byKey = [];
        foreach ($seasons as $season) {
            foreach ($season['episodes'] ?? [] as $ep) {
                $byKey[($ep['season_number'] ?? 0) . ':' . ($ep['episode_number'] ?? 0)] = $ep;
            }
        }

        foreach ($videos as &$video) {
            $ep = $byKey[$video['season'] . ':' . $video['episode']] ?? null;
            if ($ep === null) {
                continue;
            }
            if (! empty($ep['name'])) {
                $video['title'] = $ep['name'];
            }
            if (! empty($ep['overview'])) {
                $video['overview'] = $ep['overview'];
            }
            if (! empty($ep['still_path'])) {
                $video['thumbnail'] = 'https://image.tmdb.org/t/p/w300' . $ep['still_path'];
            }
            if (! empty($ep['air_date'])) {
                $video['released'] = $ep['air_date'] . 'T00:00:00.000Z';
            }
        }
        unset($video);

        usort($videos, fn ($a, $b) => [$a['season'], $a['episode']] <=> [$b['season'], $b['episode']]);

        return $videos;
    }

    private function resolveMeta(string $type, string $id): ?array
    {
        if (str_starts_with($id, 'comx:')) {
            $tmdbId = $this->comx->metaTmdbId($id);
            if ($tmdbId === null) {
                return null;
            }

            $data = $type === 'series'
                ? $this->tmdb->series($tmdbId, 'es-MX')
                : $this->tmdb->movie($tmdbId, 'es-MX');
            if (! $data) {
                // El id puede estar en el repo pero ya no en TMDB (dato podrido):
                // cae a la ficha mínima del repo para no dejar la pantalla "cargando".
                return $this->comxFallbackMeta($type, $tmdbId);
            }

            $meta = $this->comx->metaFromTmdb($data, $type, $tmdbId);
            if ($type === 'series') {
                // Sin `videos` la ficha no muestra temporadas ni se puede reproducir.
                $meta['videos'] = $this->withEpisodeDetails($tmdbId, $this->comx->seriesVideos($tmdbId));
            }

            return $meta;
        }

        if (str_starts_with($id, 'tt')) {
            $find = $this->tmdb->findByImdb($id);
            $key = $type === 'series' ? 'tv_results' : 'movie_results';
            $tmdbId = $find[$key][0]['id'] ?? null;
            if (! $tmdbId) {
                return null;
            }

            $tmdbData = $type === 'series' ? $this->tmdb->series($tmdbId) : $this->tmdb->movie($tmdbId);

            return $tmdbData ? $this->tmdb->toStremioMeta($tmdbData, $type) : null;
        }

        if (preg_match('/^yammbo:(movie|tv):(\d+)$/', $id, $m)) {
            $tmdbData = $m[1] === 'tv'
                ? $this->tmdb->series((int) $m[2])
                : $this->tmdb->movie((int) $m[2]);

            return $tmdbData ? $this->tmdb->toStremioMeta($tmdbData, $type) : null;
        }

        return null;
    }

    /** Ficha mínima con el título y los episodios del repo, cuando TMDB no tiene el id. */
    private function comxFallbackMeta(string $type, string $tmdbId): ?array
    {
        $title = $this->comx->dbTitle($type, $tmdbId);
        if ($title === null) {
            return null;
        }

        $meta = [
            'id' => 'comx:' . $tmdbId,
            'type' => $type,
            'name' => $title,
        ];
        if ($type === 'series') {
            $meta['videos'] = $this->comx->seriesVideos($tmdbId);
        }

        return $meta;
    }

    private function cors(JsonResponse $res): JsonResponse
    {
        return $res
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Cache-Control', 'public, max-age=300');
    }
}

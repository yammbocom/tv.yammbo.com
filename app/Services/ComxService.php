<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use PDO;

/**
 * Datos del addon "CO-MX": catálogos, fichas y streams a partir de las bases
 * SQLite del repo https://github.com/patitogit/CO-MX/ (Cuevana / Pelispedia /
 * Pelisjuanita). Extraído a un servicio para que StremioAddonController lo
 * reutilice y para poder probarlo con ficheros .db de prueba.
 */
class ComxService
{
    private const PAGE_SIZE = 20;

    /**
     * Fuentes del repo y sus ficheros SQLite. La clave primaria de `series` no
     * es igual en todas: cuevana y pelisjuanita usan `id_tmdb`, pelispedia usa
     * `tmdb_id`. `temporadas.serie_tmdb` guarda ese mismo valor en todas.
     */
    private const SOURCES = [
        'cuevana' => [
            'movies' => ['cuevana_peliculas.db'],
            'series' => ['cuevana_series.db'],
            'series_col' => 'id_tmdb',
            'episode_title' => true,
        ],
        'pelispedia' => [
            'movies' => ['pelispedia_peliculas.db'],
            'series' => ['pelispedia_series.db'],
            'series_col' => 'tmdb_id',
            'episode_title' => false,
        ],
        'pelisjuanita' => [
            'movies' => ['pelisjuanita_peliculas.db'],
            'series' => ['pelisjuanita_series.db'],
            'series_col' => 'id_tmdb',
            'episode_title' => true,
        ],
    ];

    /**
     * Catálogos por género. Los ids son los de TMDB (distintos para película y
     * serie) y el nombre es el que ve el usuario en la fila del inicio.
     */
    private const GENRES = [
        'movie' => [
            28 => 'Acción',
            12 => 'Aventura',
            16 => 'Animación',
            35 => 'Comedia',
            80 => 'Crimen',
            99 => 'Documental',
            18 => 'Drama',
            10751 => 'Familia',
            14 => 'Fantasía',
            36 => 'Historia',
            27 => 'Terror',
            9648 => 'Misterio',
            10402 => 'Música',
            10749 => 'Romance',
            878 => 'Ciencia ficción',
            53 => 'Suspenso',
            10752 => 'Guerra',
            37 => 'Western',
        ],
        'series' => [
            10759 => 'Acción y Aventura',
            16 => 'Animación',
            35 => 'Comedia',
            80 => 'Crimen',
            99 => 'Documental',
            18 => 'Drama',
            10751 => 'Familia',
            10762 => 'Infantil',
            9648 => 'Misterio',
            10763 => 'Noticias',
            10765 => 'Ciencia ficción y fantasía',
            10764 => 'Reality',
            10767 => 'Talk show',
            10768 => 'Guerra y política',
            37 => 'Western',
        ],
    ];

    public function __construct(
        private TmdbService $tmdb,
        private ?string $dataDir = null,
    ) {
        $this->dataDir = rtrim($dataDir ?? (string) config('comx.data_dir', '/home/tv/co-mx-work'), '/');
    }

    public function genres(): array
    {
        return self::GENRES;
    }

    /**
     * Fichas (con póster) de una página del catálogo por género. Los títulos
     * salen del discover de TMDB por popularidad y se cruzan con los .db: solo
     * se devuelven los que CO-MX tiene, para no mostrar filas que no reproducen.
     * `$skip` cuenta títulos de CO-MX (no páginas de TMDB), así que la paginación
     * no repite títulos.
     */
    public function genreMetas(string $type, int $genreId, int $skip): array
    {
        $metas = [];
        $seen = [];
        $matched = 0;
        $discoverPage = 1;
        $maxPages = 100;
        while (count($metas) < self::PAGE_SIZE && $discoverPage <= $maxPages) {
            $data = $this->tmdb->discover($type, $genreId, $discoverPage, 'es-MX');
            $results = $data['results'] ?? [];
            if ($results === []) {
                break;
            }

            $byId = [];
            $ids = [];
            foreach ($results as $item) {
                $tmdbId = (string) ($item['id'] ?? '');
                if ($tmdbId === '') {
                    continue;
                }
                $ids[] = $tmdbId;
                $byId[$tmdbId] = $item;
            }

            // array_intersect conserva el orden del discover (popularidad).
            $existing = array_values(array_intersect($ids, $this->existingIds($type, $ids)));
            foreach ($existing as $tmdbId) {
                if (isset($seen[$tmdbId])) {
                    continue;
                }
                $seen[$tmdbId] = true;
                $matched++;
                if ($matched <= $skip) {
                    continue;
                }
                $metas[] = $this->metaFromTmdb($byId[$tmdbId], $type, $tmdbId);
                if (count($metas) >= self::PAGE_SIZE) {
                    break;
                }
            }

            $discoverPage++;
        }

        return $metas;
    }

    /**
     * Populares de TMDB cruzados con el repo: solo los que CO-MX tiene, para que
     * la fila "Popular" no muestre títulos sin fuentes de reproducción.
     */
    public function popularMetas(string $type, int $skip): array
    {
        $metas = [];
        $seen = [];
        $matched = 0;
        $page = 1;
        $maxPages = 100;
        while (count($metas) < self::PAGE_SIZE && $page <= $maxPages) {
            $data = $type === 'series'
                ? $this->tmdb->popularSeries($page, 'es-MX')
                : $this->tmdb->popularMovies($page, 'es-MX');
            $results = $data['results'] ?? [];
            if ($results === []) {
                break;
            }

            $byId = [];
            $ids = [];
            foreach ($results as $item) {
                $tmdbId = (string) ($item['id'] ?? '');
                if ($tmdbId === '') {
                    continue;
                }
                $ids[] = $tmdbId;
                $byId[$tmdbId] = $item;
            }

            $existing = array_values(array_intersect($ids, $this->existingIds($type, $ids)));
            foreach ($existing as $tmdbId) {
                if (isset($seen[$tmdbId])) {
                    continue;
                }
                $seen[$tmdbId] = true;
                $matched++;
                if ($matched <= $skip) {
                    continue;
                }
                $metas[] = $this->metaFromTmdb($byId[$tmdbId], $type, $tmdbId);
                if (count($metas) >= self::PAGE_SIZE) {
                    break;
                }
            }

            $page++;
        }

        return $metas;
    }

    /**
     * Todo el catálogo de CO-MX (independiente de TMDB), ordenado del id más
     * reciente al más antiguo. Es lo que garantiza que "se ve todo el contenido"
     * aunque un título no salga en ningún discover por género.
     */
    public function allMetas(string $type, int $skip): array
    {
        $ids = $this->allIds($type);
        $pageIds = array_slice($ids, $skip, self::PAGE_SIZE);
        if ($pageIds === []) {
            return [];
        }

        $byId = $type === 'series'
            ? $this->tmdb->seriesByIds($pageIds, 'es-MX')
            : $this->tmdb->moviesByIds($pageIds, 'es-MX');

        $metas = [];
        foreach ($pageIds as $tmdbId) {
            if (! isset($byId[$tmdbId])) {
                continue;
            }
            $metas[] = $this->metaFromTmdb($byId[$tmdbId], $type, $tmdbId);
        }

        return $metas;
    }

    /**
     * Resultados de búsqueda: busca directamente en los títulos del repo (LIKE)
     * y completa el póster con TMDB en paralelo. Así devuelve todos los títulos
     * de CO-MX que cuadran, no solo los que TMDB devuelve en su búsqueda.
     */
    public function searchMetas(string $type, string $query): array
    {
        $ids = $this->searchIds($type, $query);
        if ($ids === []) {
            return [];
        }

        $tmdbIds = array_keys($ids);
        $byId = $type === 'series'
            ? $this->tmdb->seriesByIds($tmdbIds, 'es-MX')
            : $this->tmdb->moviesByIds($tmdbIds, 'es-MX');

        $metas = [];
        foreach ($ids as $tmdbId => $titulo) {
            if (isset($byId[$tmdbId])) {
                $metas[] = $this->metaFromTmdb($byId[$tmdbId], $type, $tmdbId);
            } else {
                $metas[] = [
                    'id' => 'comx:' . $tmdbId,
                    'type' => $type,
                    'name' => $titulo,
                ];
            }
        }

        return $metas;
    }

    /**
     * Ids (con su título) del repo que cuadran con la consulta, los mejores
     * primero.
     *
     * Antes era un `LIKE '%consulta%'` literal: "spider man" no encontraba
     * "Spider-Man", "casa papel" no encontraba "La casa de papel" y "pelicula"
     * no encontraba "película". Ahora se compara por palabras, sin tildes ni
     * signos, y se ordena: título exacto, luego los que empiezan por la
     * consulta, luego el resto (el más corto antes).
     */
    private function searchIds(string $type, string $query): array
    {
        $normQuery = self::normalizeTitle($query);
        // Sin letras latinas ni dígitos (cirílico, japonés…) no hay palabras que
        // comparar: se busca como antes, con LIKE literal.
        if ($normQuery === '') {
            return $this->searchIdsLike($type, $query);
        }
        // Una sola letra recorre ~55k títulos y casi todos cuadran: no aporta.
        if (strlen($normQuery) < 2) {
            return [];
        }

        // El endpoint es público y cada búsqueda recorre las tres bases con un
        // callback PHP por fila (~110 ms): la misma consulta se sirve de caché.
        return Cache::remember('comx:search:v1:' . $type . ':' . md5($normQuery), 3600, fn (): array => $this->scoreSearchIds($type, $normQuery));
    }

    /** @return array<string,string> id TMDB => título */
    private function scoreSearchIds(string $type, string $normQuery): array
    {
        $tokens = array_values(array_unique(explode(' ', $normQuery)));
        $compactQuery = str_replace(' ', '', $normQuery);
        $score = static fn ($title): int => self::titleScore((string) $title, $normQuery, $compactQuery, $tokens);

        $found = [];
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            [$table, $col, $file] = $type === 'movie'
                ? ['peliculas', 'id_tmdb', $src['movies'][0]]
                : ['series', $src['series_col'], $src['series'][0]];

            $path = $this->dbPath($file);
            if (! is_file($path)) {
                continue;
            }

            $pdo = $this->open($path);
            $pdo->sqliteCreateFunction('yammbo_score', $score, 1, PDO::SQLITE_DETERMINISTIC);
            $stmt = $pdo->query("SELECT id_tmdb, titulo, s FROM (SELECT {$col} AS id_tmdb, titulo, yammbo_score(titulo) AS s FROM {$table}) WHERE s > 0 ORDER BY s DESC LIMIT 40");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $id = (string) $row['id_tmdb'];
                if (preg_match('/^\d+$/', $id) && (! isset($found[$id]) || $found[$id][1] < (int) $row['s'])) {
                    $found[$id] = [$row['titulo'], (int) $row['s']];
                }
            }
        }

        uasort($found, static fn (array $a, array $b): int => $b[1] <=> $a[1]);

        return array_map(static fn (array $hit): string => $hit[0], array_slice($found, 0, 40, true));
    }

    /** La búsqueda de antes: título que contiene la consulta tal cual. */
    private function searchIdsLike(string $type, string $query): array
    {
        $ids = [];
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            [$table, $col, $file] = $type === 'movie'
                ? ['peliculas', 'id_tmdb', $src['movies'][0]]
                : ['series', $src['series_col'], $src['series'][0]];

            $path = $this->dbPath($file);
            if (! is_file($path)) {
                continue;
            }

            $stmt = $this->open($path)->prepare("SELECT {$col} AS id_tmdb, titulo FROM {$table} WHERE titulo LIKE :q LIMIT 40");
            $stmt->execute([':q' => '%' . trim($query) . '%']);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $id = (string) $row['id_tmdb'];
                if (preg_match('/^\d+$/', $id) && ! isset($ids[$id])) {
                    $ids[$id] = $row['titulo'];
                }
            }

            if (count($ids) >= 40) {
                break;
            }
        }

        return $ids;
    }

    /** "Spider-Man: Lejos de Casa" → "spider man lejos de casa". */
    private static function normalizeTitle(string $title): string
    {
        $title = mb_strtolower($title, 'UTF-8');
        $title = strtr($title, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $title));
    }

    /**
     * 0 si el título no cuadra. Cuadra si contiene todas las palabras de la
     * consulta (también pegadas: "spiderman" cuadra con "Spider-Man").
     */
    private static function titleScore(string $title, string $query, string $compactQuery, array $tokens): int
    {
        $norm = self::normalizeTitle($title);
        $compact = str_replace(' ', '', $norm);
        if ($norm === $query || $compact === $compactQuery) {
            $rank = 4;
        } else {
            foreach ($tokens as $token) {
                if (! str_contains($norm, $token) && ! str_contains($compact, $token)) {
                    return 0;
                }
            }
            if (str_starts_with($norm, $query) || str_starts_with($compact, $compactQuery)) {
                $rank = 3;
            } elseif (str_contains(' ' . $norm . ' ', ' ' . $query . ' ')) {
                $rank = 2;
            } else {
                $rank = 1;
            }
        }

        return $rank * 1000 - min(strlen($norm), 999);
    }

    /** Título guardado en el repo, para fichas cuyo id de TMDB ya no existe. */
    public function dbTitle(string $type, string $tmdbId): ?string
    {
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            [$table, $col, $file] = $type === 'movie'
                ? ['peliculas', 'id_tmdb', $src['movies'][0]]
                : ['series', $src['series_col'], $src['series'][0]];

            $path = $this->dbPath($file);
            if (! is_file($path)) {
                continue;
            }

            $pdo = $this->open($path);
            $stmt = $pdo->prepare("SELECT titulo FROM {$table} WHERE {$col} = :id LIMIT 1");
            $stmt->execute([':id' => (string) $tmdbId]);
            $title = $stmt->fetchColumn();
            if (is_string($title) && $title !== '') {
                return $title;
            }
        }

        return null;
    }

    public function metaFromTmdb(array $tmdb, string $type, string $tmdbId): array
    {
        $title = $tmdb['title'] ?? $tmdb['name'] ?? 'Untitled';
        $released = $tmdb['release_date'] ?? $tmdb['first_air_date'] ?? null;

        return [
            'id' => 'comx:' . $tmdbId,
            'type' => $type,
            'name' => $title,
            'poster' => isset($tmdb['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $tmdb['poster_path'] : null,
            'posterShape' => 'regular',
            'background' => isset($tmdb['backdrop_path']) ? 'https://image.tmdb.org/t/p/original' . $tmdb['backdrop_path'] : null,
            'description' => $tmdb['overview'] ?? null,
            'releaseInfo' => $released ? substr($released, 0, 4) : null,
            'imdbRating' => isset($tmdb['vote_average']) ? (string) round($tmdb['vote_average'], 1) : null,
            'genres' => $this->genreNames($tmdb, $type),
        ];
    }

    /**
     * Temporadas y episodios de una serie tal y como están en el repo (solo los
     * que tienen al menos un enlace). El id de cada vídeo es el que espera
     * seriesStreams(): comx:{tmdbId}:{season}:{episode}.
     *
     * Ojo: `enlaces` no tiene índice por episodio_id en el repo, así que el
     * EXISTS correlacionado por episodio hacía un full-scan de enlaces por cada
     * episodio (minutos en series largas). Aquí se resuelve en dos pasadas:
     * episodios (indexado) y luego un único IN sobre enlaces.
     */
    public function seriesVideos(string $tmdbId): array
    {
        $videos = [];
        $seen = [];
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            $path = $this->dbPath($src['series'][0]);
            if (! is_file($path)) {
                continue;
            }

            // pelispedia no guarda el título del episodio; las otras dos sí.
            $titleCol = $src['episode_title'] ? 'e.titulo' : "''";

            $pdo = $this->open($path);

            $stmt = $pdo->prepare(
                "SELECT e.id AS episode_id, t.numero AS season, e.numero AS episode, {$titleCol} AS title
                   FROM temporadas t
                   JOIN episodios e ON e.temporada_id = t.id
                  WHERE t.serie_tmdb = :id
                  ORDER BY t.numero, e.numero"
            );
            $stmt->execute([':id' => (string) $tmdbId]);
            $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($episodes === []) {
                continue;
            }

            $ids = array_values(array_map(fn ($e) => (int) $e['episode_id'], $episodes));
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT DISTINCT episodio_id FROM enlaces WHERE episodio_id IN ({$in})");
            $stmt->execute($ids);
            $linked = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));

            foreach ($episodes as $row) {
                if (! isset($linked[(int) $row['episode_id']])) {
                    continue;
                }
                $season = (int) $row['season'];
                $episode = (int) $row['episode'];
                $key = $season . ':' . $episode;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $videos[] = [
                    'id' => "comx:{$tmdbId}:{$season}:{$episode}",
                    'title' => $row['title'] !== '' ? $row['title'] : "T{$season} E{$episode}",
                    'season' => $season,
                    'episode' => $episode,
                ];
            }
        }

        return $videos;
    }

    /**
     * Streams de CO-MX. Para película: comx:{tmdbId}. Para serie:
     * comx:{tmdbId}:{season}:{episode}. Devuelve la página embed en `url` (nunca
     * vídeo directo: el hoster no entrega m3u8 a una IP de datacenter); el front
     * detecta `behaviorHints.yammboEmbed` y la monta en un iframe.
     */
    public function streams(string $type, string $id): array
    {
        return $type === 'series'
            ? $this->seriesStreams($id)
            : $this->movieStreams($id);
    }

    /** Id de meta "comx:N" -> tmdbId, o null si no tiene el formato esperado. */
    public function metaTmdbId(string $id): ?string
    {
        return preg_match('/^comx:(\d+)$/', $id, $m) ? $m[1] : null;
    }

    /** Id de episodio "comx:N:S:E" -> [tmdbId, season, episode], o null. */
    public function seriesEpisodeId(string $id): ?array
    {
        if (! preg_match('/^comx:(\d+):(\d+):(\d+)$/', $id, $m)) {
            return null;
        }

        return [(string) $m[1], (int) $m[2], (int) $m[3]];
    }

    // --- catálogo -------------------------------------------------------

    private function allIds(string $type): array
    {
        $ids = [];
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            [$table, $col, $file] = $type === 'movie'
                ? ['peliculas', 'id_tmdb', $src['movies'][0]]
                : ['series', $src['series_col'], $src['series'][0]];

            $path = $this->dbPath($file);
            if (! is_file($path)) {
                continue;
            }

            $pdo = $this->open($path);
            $stmt = $pdo->query("SELECT {$col} FROM {$table}");
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $id = (string) $id;
                if (preg_match('/^\d+$/', $id)) {
                    $ids[$id] = true;
                }
            }
        }

        $ids = array_map('strval', array_keys($ids));
        usort($ids, fn ($a, $b) => (int) $b <=> (int) $a);

        return $ids;
    }

    /** De una lista de ids TMDB, cuáles existen en alguna fuente del repo. */
    public function existingIds(string $type, array $tmdbIds): array
    {
        $tmdbIds = array_values(array_filter(array_map('strval', $tmdbIds), fn ($id) => $id !== ''));
        if ($tmdbIds === []) {
            return [];
        }

        $found = [];
        foreach (array_keys(self::SOURCES) as $source) {
            $src = self::SOURCES[$source];
            [$table, $col, $file] = $type === 'movie'
                ? ['peliculas', 'id_tmdb', $src['movies'][0]]
                : ['series', $src['series_col'], $src['series'][0]];

            $path = $this->dbPath($file);
            if (! is_file($path)) {
                continue;
            }

            $pdo = $this->open($path);
            $in = implode(',', array_fill(0, count($tmdbIds), '?'));
            $stmt = $pdo->prepare("SELECT {$col} FROM {$table} WHERE {$col} IN ({$in})");
            $stmt->execute($tmdbIds);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $found[(string) $id] = true;
            }
        }

        return array_keys($found);
    }

    // --- streams --------------------------------------------------------

    private function movieStreams(string $id): array
    {
        $tmdbId = $this->metaTmdbId($id);
        if ($tmdbId === null) {
            return [];
        }

        return $this->aggregatorStreams('movie', $tmdbId);
    }

    private function seriesStreams(string $id): array
    {
        $parts = $this->seriesEpisodeId($id);
        if ($parts === null) {
            return [];
        }
        [$tmdbId, $season, $episode] = $parts;

        return $this->aggregatorStreams('series', $tmdbId, $season, $episode);
    }

    private function aggregatorStreams(string $type, string $tmdbId, ?int $season = null, ?int $episode = null): array
    {
        $url = $type === 'movie'
            ? "https://api.moviemaster.lat/e/movie/{$tmdbId}?autoplay=1"
            : "https://api.moviemaster.lat/e/tv/{$tmdbId}/{$season}/{$episode}?autoplay=1";

        return [[
            'name' => 'Ver ahora',
            'title' => 'Ver ahora',
            'url' => $url,
            'behaviorHints' => [
                'yammboEmbed' => true,
            ],
        ]];
    }

    // --- helpers --------------------------------------------------------

    private function genreNames(array $tmdb, string $type): array
    {
        if (isset($tmdb['genres']) && is_array($tmdb['genres'])) {
            return array_column($tmdb['genres'], 'name');
        }

        $names = [];
        foreach ($tmdb['genre_ids'] ?? [] as $id) {
            if (isset(self::GENRES[$type][(int) $id])) {
                $names[] = self::GENRES[$type][(int) $id];
            }
        }

        return $names;
    }

    private function open(string $path): PDO
    {
        return new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private function dbPath(string $file): string
    {
        return $this->dataDir . '/' . $file;
    }
}

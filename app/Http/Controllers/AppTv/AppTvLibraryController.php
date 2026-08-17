<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Yammbo Library API — independiente de Stremio Cloud.
 * Los React components Library/Calendar consumen estos endpoints.
 *
 * Auth: la resuelve ResolveAppTvUser (sesión web o JWT Bearer) y la deja en el
 *       atributo `yambo_user_id`. El user_id que venga en el request se ignora.
 */
class AppTvLibraryController extends Controller
{
    /**
     * Dueño autenticado de la petición, puesto por ResolveAppTvUser.
     */
    private function userId(Request $request): int
    {
        return (int) $request->attributes->get('yambo_user_id');
    }

    /**
     * GET /api/app-tv/library?type=movie|series|all
     * Lista los items en la biblioteca del usuario autenticado (no removidos).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->userId($request);

        $type = $request->query('type', 'all');
        $query = DB::table('yambo_library')
            ->where('user_id', $userId)
            ->whereNull('removed_at');

        if (in_array($type, ['movie', 'series', 'channel'], true)) {
            $query->where('meta_type', $type);
        }

        $items = $query->orderByDesc('added_at')->get()->map(function ($row) {
            return $this->formatItem($row);
        });

        return response()->json(['items' => $items->values()]);
    }

    /**
     * GET /api/app-tv/library/status?meta_id=Y&meta_type=Z
     * Devuelve si el item está en la biblioteca del usuario (no removido).
     * Usado por el MetaDetails para mostrar "Add" vs "Remove" en el botón.
     */
    public function status(Request $request): JsonResponse
    {
        $userId = $this->userId($request);
        $metaId = (string) $request->query('meta_id', '');
        $metaType = (string) $request->query('meta_type', '');

        if ($metaId === '' || $metaType === '') {
            return response()->json(['in_library' => false], 200);
        }

        $exists = DB::table('yambo_library')
            ->where('user_id', $userId)
            ->where('meta_id', $metaId)
            ->where('meta_type', $metaType)
            ->whereNull('removed_at')
            ->exists();

        return response()->json(['in_library' => $exists]);
    }

    /**
     * POST /api/app-tv/library/toggle
     * Body: { meta_id, meta_type, meta_name, meta_poster?, meta_background?, meta_year?, meta_rating?, meta_runtime?, meta_genres? }
     * Si existe y no está removido → lo remueve. Si existe removido → lo re-activa. Si no existe → lo crea.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meta_id' => 'required|string|max:191',
            'meta_type' => 'required|in:movie,series,channel',
            'meta_name' => 'required|string|max:255',
            'meta_poster' => 'nullable|string|max:512',
            'meta_background' => 'nullable|string|max:512',
            'meta_year' => 'nullable|string|max:16',
            'meta_rating' => 'nullable|numeric',
            'meta_runtime' => 'nullable|integer|min:0',
            'meta_genres' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $this->userId($request);

        $existing = DB::table('yambo_library')
            ->where('user_id', $data['user_id'])
            ->where('meta_id', $data['meta_id'])
            ->where('meta_type', $data['meta_type'])
            ->first();

        if ($existing) {
            if ($existing->removed_at !== null) {
                // Re-activate
                DB::table('yambo_library')->where('id', $existing->id)->update([
                    'removed_at' => null,
                    'added_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                if ($data['meta_type'] === 'series') {
                    $this->syncSeriesEpisodesFromCinemeta($data['user_id'], $data['meta_id']);
                }

                return response()->json([
                    'action' => 'added',
                    'item' => $this->formatItem(DB::table('yambo_library')->where('id', $existing->id)->first()),
                ]);
            }

            // Remove (soft)
            DB::table('yambo_library')->where('id', $existing->id)->update([
                'removed_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json(['action' => 'removed', 'meta_id' => $data['meta_id']]);
        }

        // Create new
        $id = DB::table('yambo_library')->insertGetId([
            'user_id' => $data['user_id'],
            'meta_id' => $data['meta_id'],
            'meta_type' => $data['meta_type'],
            'meta_name' => $data['meta_name'],
            'meta_poster' => $data['meta_poster'] ?? null,
            'meta_background' => $data['meta_background'] ?? null,
            'meta_year' => $data['meta_year'] ?? null,
            'meta_rating' => $data['meta_rating'] ?? null,
            'meta_runtime' => $data['meta_runtime'] ?? null,
            'meta_genres' => isset($data['meta_genres']) ? json_encode($data['meta_genres']) : null,
            'added_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        if ($data['meta_type'] === 'series') {
            $this->syncSeriesEpisodesFromCinemeta($data['user_id'], $data['meta_id']);
        }

        return response()->json([
            'action' => 'added',
            'item' => $this->formatItem(DB::table('yambo_library')->where('id', $id)->first()),
        ]);
    }

    /**
     * Consulta Cinemeta (addon público gratuito — mismo dataset que usa Stremio core)
     * y popula yambo_library_episodes al agregar una serie. Best-effort — no bloquea
     * el toggle si falla (red down, serie sin metadata, etc.).
     */
    private function syncSeriesEpisodesFromCinemeta(int $userId, string $metaId): void
    {
        try {
            $resp = Http::timeout(8)->get("https://v3-cinemeta.strem.io/meta/series/{$metaId}.json");
            if (! $resp->ok()) {
                return;
            }

            $videos = data_get($resp->json(), 'meta.videos', []);
            if (! is_array($videos) || empty($videos)) {
                return;
            }

            $now = Carbon::now();
            foreach ($videos as $v) {
                $season = isset($v['season']) ? (int) $v['season'] : null;
                $episode = isset($v['episode']) ? (int) $v['episode'] : null;
                if ($season === null || $episode === null) {
                    continue;
                }

                $airDate = null;
                if (! empty($v['released'])) {
                    try {
                        $airDate = Carbon::parse($v['released'])->toDateString();
                    } catch (\Throwable $e) {
                        $airDate = null;
                    }
                }

                DB::table('yambo_library_episodes')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'meta_id' => $metaId,
                        'season' => $season,
                        'episode' => $episode,
                    ],
                    [
                        'episode_name' => isset($v['name']) ? mb_substr((string) $v['name'], 0, 255) : null,
                        'air_date' => $airDate,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Cinemeta sync failed', ['meta_id' => $metaId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/app-tv/library/progress
     * Body: { meta_id, meta_type, watch_progress, completed? }
     * Actualiza el progreso de reproducción.
     */
    public function progress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meta_id' => 'required|string|max:191',
            'meta_type' => 'required|in:movie,series,channel',
            'watch_progress' => 'required|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $this->userId($request);

        $updated = DB::table('yambo_library')
            ->where('user_id', $data['user_id'])
            ->where('meta_id', $data['meta_id'])
            ->where('meta_type', $data['meta_type'])
            ->update([
                'watch_progress' => $data['watch_progress'],
                'watched_at' => ! empty($data['completed']) ? Carbon::now() : null,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json(['updated' => (bool) $updated]);
    }

    /**
     * GET /api/app-tv/calendar?from=YYYY-MM-DD&to=YYYY-MM-DD
     * Devuelve episodios próximos/recientes de las series en la biblioteca del usuario.
     * Si no existen rows en yambo_library_episodes para el rango pedido, el cliente
     * debe llenarlos vía POST /library/sync-series-episodes (se completan on-demand).
     */
    public function calendar(Request $request): JsonResponse
    {
        $userId = $this->userId($request);

        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->addDays(60)->toDateString());

        try {
            $fromDate = Carbon::parse($from)->toDateString();
            $toDate = Carbon::parse($to)->toDateString();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'invalid date'], 422);
        }

        $episodes = DB::table('yambo_library_episodes')
            ->where('user_id', $userId)
            ->whereBetween('air_date', [$fromDate, $toDate])
            ->orderBy('air_date')
            ->get();

        // Merge con meta (poster, serie name) de yambo_library
        $metaMap = DB::table('yambo_library')
            ->where('user_id', $userId)
            ->where('meta_type', 'series')
            ->whereNull('removed_at')
            ->get()
            ->keyBy('meta_id');

        $events = $episodes->map(function ($ep) use ($metaMap) {
            $parent = $metaMap->get($ep->meta_id);

            return [
                'id' => $ep->id,
                'meta_id' => $ep->meta_id,
                'season' => (int) $ep->season,
                'episode' => (int) $ep->episode,
                'episode_name' => $ep->episode_name,
                'air_date' => $ep->air_date,
                'watched_at' => $ep->watched_at,
                'watch_progress' => (int) $ep->watch_progress,
                'series_name' => $parent->meta_name ?? null,
                'series_poster' => $parent->meta_poster ?? null,
            ];
        });

        return response()->json(['events' => $events->values()]);
    }

    /**
     * POST /api/app-tv/library/sync-episodes
     * Body: { meta_id, episodes: [{season, episode, episode_name?, air_date?}, ...] }
     * Upsert bulk de episodios (llamado por el cliente cuando consulta TMDB / addon).
     */
    public function syncEpisodes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meta_id' => 'required|string|max:191',
            'episodes' => 'required|array|max:500',
            'episodes.*.season' => 'required|integer|min:0',
            'episodes.*.episode' => 'required|integer|min:0',
            'episodes.*.episode_name' => 'nullable|string|max:255',
            'episodes.*.air_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $this->userId($request);
        $now = Carbon::now();

        foreach ($data['episodes'] as $ep) {
            DB::table('yambo_library_episodes')->updateOrInsert(
                [
                    'user_id' => $data['user_id'],
                    'meta_id' => $data['meta_id'],
                    'season' => $ep['season'],
                    'episode' => $ep['episode'],
                ],
                [
                    'episode_name' => $ep['episode_name'] ?? null,
                    'air_date' => $ep['air_date'] ?? null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        return response()->json(['synced' => count($data['episodes'])]);
    }

    private function formatItem($row): array
    {
        return [
            'id' => (int) $row->id,
            'meta_id' => $row->meta_id,
            'meta_type' => $row->meta_type,
            'name' => $row->meta_name,
            'poster' => $row->meta_poster,
            'background' => $row->meta_background,
            'year' => $row->meta_year,
            'rating' => $row->meta_rating !== null ? (float) $row->meta_rating : null,
            'runtime' => $row->meta_runtime !== null ? (int) $row->meta_runtime : null,
            'genres' => $row->meta_genres ? json_decode($row->meta_genres, true) : [],
            'added_at' => $row->added_at,
            'watched_at' => $row->watched_at,
            'watch_progress' => (int) $row->watch_progress,
        ];
    }
}

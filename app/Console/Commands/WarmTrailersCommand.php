<?php

namespace App\Console\Commands;

use App\Http\Controllers\AppTv\TrailerController;
use App\Services\ComxService;
use App\Services\TmdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Calienta la caché de tráilers de los catálogos CO-MX que sirve el addon público
 * (populares y "todo", películas y series), con la misma clave que usa la ficha
 * (StremioAddonController::withTrailer), para que la ficha lo encuentre ya hecho.
 */
class WarmTrailersCommand extends Command
{
    protected $signature = 'yambo:warm-trailers
        {--limit=300 : Máximo de títulos a revisar}
        {--dry-run : Solo muestra qué calentaría, sin llamar a TMDB/YouTube para el tráiler}';

    protected $description = 'Precalcula en caché el tráiler de los títulos de los catálogos CO-MX';

    private const PAGE_SIZE = 20;
    private const MAX_PAGES = 50;
    private const PAUSE_MICROSECONDS = 200000;

    public function handle(ComxService $comx, TmdbService $tmdb, TrailerController $trailers): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dry = (bool) $this->option('dry-run');

        $stats = ['reviewed' => 0, 'cached' => 0, 'new' => 0, 'none' => 0, 'errors' => 0];
        $seen = [];

        foreach ($this->candidates($comx, $limit, $seen) as [$type, $tmdbId]) {
            $stats['reviewed']++;
            try {
                // Mismos datos que resolveMeta(): ficha es-MX de TMDB (cacheada) → título, año y original.
                $data = $type === 'series' ? $tmdb->series((int) $tmdbId, 'es-MX') : $tmdb->movie((int) $tmdbId, 'es-MX');
                if (! $data) {
                    $stats['errors']++;
                    $this->line("  error   {$type} {$tmdbId}: sin ficha en TMDB");
                    continue;
                }
                $meta = $comx->metaFromTmdb($data, $type, (string) $tmdbId);
                $title = (string) ($meta['name'] ?? '');
                $orig = (string) ($data['original_title'] ?? $data['original_name'] ?? '');
                $year = (int) ($meta['releaseInfo'] ?? 0);
                $kind = $type === 'series' ? 'tv' : 'movie';

                $ck = $trailers->cacheKey($title, $orig, $year, $kind, 'es', (int) $tmdbId);
                if (Cache::has($ck)) {
                    $stats['cached']++;
                    continue;
                }

                if ($dry) {
                    $stats['new']++;
                    $this->line("  faltaría {$type} {$tmdbId}: {$title} ({$year})");
                    continue;
                }

                $found = $trailers->trailerFor($title, $orig, $year, $kind, 'es', (int) $tmdbId);
                if (! empty($found['ok']) && ! empty($found['key'])) {
                    $stats['new']++;
                    $this->line("  nuevo   {$type} {$tmdbId}: {$title} ({$year}) -> {$found['key']}");
                } else {
                    $stats['none']++;
                    $this->line("  sin tráiler {$type} {$tmdbId}: {$title} ({$year})");
                }
                usleep(self::PAUSE_MICROSECONDS);
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->line("  error   {$type} {$tmdbId}: " . $e->getMessage());
            }
        }

        $label = $dry ? ' (dry-run: "nuevos" = faltan en caché)' : '';
        $this->info(sprintf(
            'Revisados: %d | ya en caché: %d | nuevos: %d | sin tráiler: %d | errores: %d%s',
            $stats['reviewed'], $stats['cached'], $stats['new'], $stats['none'], $stats['errors'], $label
        ));

        return self::SUCCESS;
    }

    /**
     * Títulos [type, tmdbId] de las primeras páginas de los catálogos principales
     * del addon (populares y todo, películas y series), alternando fuentes página a
     * página para que un límite bajo reparta entre todas.
     */
    private function candidates(ComxService $comx, int $limit, array &$seen): \Generator
    {
        $sources = [
            ['movie', fn (int $skip) => $comx->popularMetas('movie', $skip)],
            ['series', fn (int $skip) => $comx->popularMetas('series', $skip)],
            ['movie', fn (int $skip) => $comx->allMetas('movie', $skip)],
            ['series', fn (int $skip) => $comx->allMetas('series', $skip)],
        ];
        $yielded = 0;

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $any = false;
            foreach ($sources as [$type, $fetch]) {
                try {
                    $metas = $fetch($page * self::PAGE_SIZE);
                } catch (\Throwable $e) {
                    $this->warn("  catálogo {$type} página {$page}: " . $e->getMessage());
                    continue;
                }
                $any = $any || $metas !== [];
                foreach ($metas as $meta) {
                    $tmdbId = $comx->metaTmdbId((string) ($meta['id'] ?? ''));
                    if ($tmdbId === null || isset($seen["{$type}:{$tmdbId}"])) {
                        continue;
                    }
                    $seen["{$type}:{$tmdbId}"] = true;
                    yield [$type, $tmdbId];
                    if (++$yielded >= $limit) {
                        return;
                    }
                }
            }
            if (! $any) {
                return;
            }
        }
    }
}

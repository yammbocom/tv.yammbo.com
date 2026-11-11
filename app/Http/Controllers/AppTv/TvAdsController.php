<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\TvAd;
use App\Support\TvAudience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Anuncios propios antes de una pelicula o capitulo.
 *
 *   GET  /api/app-tv/ads/next?t=     {enabled, gap_minutes, ad:{id,video,skip_after,link_text}|null}
 *   POST /api/app-tv/ads/event       id, ev=impression|complete|skip
 *
 * La app decide cuando toca (respeta gap_minutes entre anuncios en ese televisor).
 */
class TvAdsController extends Controller
{
    public static function settings(): array
    {
        $def = ['enabled' => true, 'gap_minutes' => 30];
        try {
            $raw = @file_get_contents(storage_path('app/tv-ads.json'));
            $j = $raw ? json_decode($raw, true) : null;
            if (is_array($j)) {
                return [
                    'enabled' => (bool) ($j['enabled'] ?? true),
                    'gap_minutes' => max(0, min(1440, (int) ($j['gap_minutes'] ?? 30))),
                ];
            }
        } catch (\Throwable $e) {
        }

        return $def;
    }

    public function next(Request $request): JsonResponse
    {
        $s = self::settings();
        $out = ['enabled' => $s['enabled'], 'gap_minutes' => $s['gap_minutes'], 'ad' => null];
        if (! $s['enabled']) {
            return response()->json($out);
        }

        $segment = TvAudience::segment(TvAudience::user((string) $request->query('t', '')));
        $ads = Cache::remember('tv_ads_v1_'.$segment, 120, fn () => TvAd::liveFor($segment)->get(['id', 'video_url', 'skip_after', 'link_text', 'weight'])->toArray());
        if (! $ads) {
            return response()->json($out);
        }

        // al azar, con peso
        $total = array_sum(array_map(fn ($a) => max(1, (int) $a['weight']), $ads));
        $r = random_int(1, max(1, $total));
        $pick = $ads[0];
        foreach ($ads as $a) {
            $r -= max(1, (int) $a['weight']);
            if ($r <= 0) {
                $pick = $a;
                break;
            }
        }
        // los subidos al panel van como ruta relativa: la app los pide por su propio dominio
        $video = $pick['video_url'];
        $out['ad'] = [
            'id' => $pick['id'],
            'video' => $video,
            'skip_after' => (int) $pick['skip_after'],
            'link_text' => $pick['link_text'] ?: '',
        ];

        return response()->json($out);
    }

    public function event(Request $request): JsonResponse
    {
        $ev = (string) $request->input('ev');
        $col = ['impression' => 'impressions', 'complete' => 'completions', 'skip' => 'skips'][$ev] ?? null;
        $id = (int) $request->input('id');
        if (! $col || $id <= 0) {
            return response()->json(['ok' => true]);
        }
        // solo cuentas de la app, y cada una cuenta como mucho una vez por minuto y anuncio/evento:
        // asi las cifras del panel no se pueden inflar desde fuera
        $user = TvAudience::user((string) $request->input('t', ''));
        if ($user && Cache::add("tv_ad_ev:{$user->id}:{$id}:{$ev}", 1, 60)) {
            TvAd::whereKey($id)->where('active', true)->increment($col);
        }

        return response()->json(['ok' => true]);
    }
}

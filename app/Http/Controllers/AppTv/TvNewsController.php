<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\TvNews;
use App\Support\TvAudience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * GET /api/app-tv/news?t=<jwt>&likes=Accion,Comedia
 * Novedades de la app de TV: la mas reciente con popup se muestra al abrir; todas en la campana.
 * Las que tienen genero solo van a perfiles que ven ese genero (likes = los que mas ve el perfil).
 */
class TvNewsController extends Controller
{
    private const MONTHS = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.'];

    public function __invoke(Request $request): JsonResponse
    {
        $user = TvAudience::user((string) $request->query('t', ''));
        $segment = TvAudience::segment($user);
        $likes = self::likes((string) $request->query('likes', ''));
        if ($user && $likes) {
            self::rememberGenres($user->id, $likes);
        }

        $items = Cache::remember('tv_news_v1_'.$segment, 120, function () use ($segment) {
            return TvNews::liveFor($segment)
                ->orderByRaw('COALESCE(starts_at, created_at) DESC')
                ->take(30)
                ->get()
                ->map(function (TvNews $n) {
                    $d = $n->starts_at ?: $n->created_at;

                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'body' => $n->body,
                        'image' => $n->image_url ?: '',
                        'action' => $n->action,
                        'action_value' => $n->action_value ?: '',
                        'popup' => $n->popup,
                        'genre' => $n->genre ? self::key($n->genre) : '',
                        'date' => $d ? $d->day.' '.self::MONTHS[$d->month - 1] : '',
                        'ts' => $d ? $d->timestamp : 0,
                    ];
                })->values()->all();
        });

        $items = array_values(array_filter($items, fn ($n) => ($n['genre'] ?? '') === '' || isset($likes[$n['genre']])));
        $items = array_map(function ($n) {
            unset($n['genre']);

            return $n;
        }, $items);

        return response()->json(['items' => $items]);
    }

    /** "Acción" y "accion" son el mismo genero. */
    public static function key(string $s): string
    {
        return Str::lower(Str::ascii(trim($s)));
    }

    /** likes de la app -> [clave => nombre], como mucho 10. */
    private static function likes(string $raw): array
    {
        $out = [];
        foreach (array_slice(explode(',', $raw), 0, 10) as $g) {
            $g = trim(mb_substr($g, 0, 40));
            if ($g !== '') {
                $out[self::key($g)] = $g;
            }
        }

        return $out;
    }

    /**
     * Generos que ven de verdad los usuarios, para sugerirlos en el panel (una vez al dia por cuenta).
     */
    private static function rememberGenres(int $userId, array $likes): void
    {
        if (! Cache::add('tv_genres_rep:'.$userId, 1, 86400)) {
            return;
        }
        $seen = Cache::get('tv_seen_genres', []);
        foreach ($likes as $k => $name) {
            $seen[$k] = ['name' => $seen[$k]['name'] ?? $name, 'n' => ($seen[$k]['n'] ?? 0) + 1];
        }
        uasort($seen, fn ($a, $b) => $b['n'] <=> $a['n']);
        Cache::forever('tv_seen_genres', array_slice($seen, 0, 80, true));
    }
}

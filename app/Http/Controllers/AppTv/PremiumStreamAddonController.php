<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\YamboAddonToken;
use App\Services\ComxService;
use App\Support\YamboSubscription;
use Illuminate\Http\Request;

/**
 * Addon de streams Premium por usuario (com.yammbo.premium):
 *
 *   https://tv.yammbo.com/yp/{token}/manifest.json
 *   https://tv.yammbo.com/yp/{token}/stream/{type}/{id}.json
 *
 * Sustituye al proxy AIOStreams (AddonProxyController, /aio/...), cuyo proveedor
 * ya no responde. Misma regla de acceso: token de la cuenta (revocable) y
 * suscripcion activa (Premium o prueba), si no 403. Los streams son los de
 * CO-MX (ComxService), los mismos que servia el addon publico para ids `comx:`.
 *
 * Respuestas siempre `private, no-store`: dependen de la suscripcion de quien
 * pregunta, asi que ninguna cache compartida (CDN) debe guardarlas.
 */
class PremiumStreamAddonController extends Controller
{
    public function __construct(private ComxService $comx)
    {
    }

    public function handle(Request $request, string $token, string $path = 'manifest.json')
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->cors(response('', 204));
        }

        $row = YamboAddonToken::where('token', $token)->whereNull('revoked_at')->first();
        if (! $row) {
            return $this->json(['err' => 'Invalid or revoked addon token'], 403);
        }

        $user = User::find($row->user_id);
        if (! $user || ! YamboSubscription::isActive($user)) {
            return $this->json(['err' => 'Subscription is not active'], 403);
        }

        $this->track($row, $request);

        $path = ltrim($path, '/');
        if ($path === '' || $path === 'manifest.json') {
            return $this->manifest($request);
        }

        if (preg_match('#^stream/(movie|series)/([^/]+)\.json$#', $path, $m)) {
            return $this->stream($m[1], $m[2]);
        }

        return $this->json(['err' => 'Not found'], 404);
    }

    private function manifest(Request $request)
    {
        $spanish = ! str_starts_with(strtolower((string) $request->header('Accept-Language', 'es')), 'en');

        return $this->json([
            'id' => 'com.yammbo.premium',
            'version' => '1.0.0',
            'name' => 'Yammbo Premium',
            'description' => $spanish
                ? 'Reproducción incluida con tu suscripción a Yammbo Tv. Se activa y se desactiva sola según tu plan.'
                : 'Playback included with your Yammbo Tv subscription. Enabled and disabled automatically with your plan.',
            'logo' => url('/images/yambo-icon.png'),
            'resources' => ['stream'],
            'types' => ['movie', 'series'],
            'idPrefixes' => ['comx'],
            'catalogs' => [],
            'behaviorHints' => [
                'configurable' => false,
                'configurationRequired' => false,
            ],
        ]);
    }

    private function stream(string $type, string $id)
    {
        // Solo CO-MX trae fuentes propias; el resto de ids no tiene streams.
        $streams = str_starts_with($id, 'comx:') ? $this->comx->streams($type, $id) : [];

        return $this->json(['streams' => $streams]);
    }

    /**
     * Same tracking as the /aio proxy: counts distinct IPs per token and day
     * and logs at error level (LOG_LEVEL=error) when a URL seems to circulate.
     */
    private function track(YamboAddonToken $row, Request $request): void
    {
        $today = \Carbon\Carbon::now()->toDateString();
        $hash = substr(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')), 0, 16);

        $ips = is_array($row->ips) ? $row->ips : [];
        $seenToday = $ips[$today] ?? [];
        $isNewIp = ! in_array($hash, $seenToday, true);
        $todayIps = $isNewIp ? array_merge($seenToday, [$hash]) : $seenToday;

        $stale = $row->last_used_at === null || $row->last_used_at->lt(\Carbon\Carbon::now()->subMinute());
        if (! $isNewIp && ! $stale) {
            return;
        }

        $max = (int) config('yammbo.aio.max_ips_per_day', 8);
        if ($isNewIp && count($todayIps) > $max) {
            \Log::error('[premium-addon] token con muchas IPs distintas hoy', [
                'user_id' => $row->user_id,
                'distinct_ips' => count($todayIps),
                'max' => $max,
            ]);
        }

        $row->forceFill([
            'ips' => [$today => array_slice($todayIps, -50)],
            'last_used_at' => \Carbon\Carbon::now(),
            'hits' => $row->hits + 1,
        ])->save();
    }

    private function json(array $data, int $status = 200)
    {
        return $this->cors(response()->json($data, $status, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function cors($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Cache-Control', 'private, no-store');
    }
}

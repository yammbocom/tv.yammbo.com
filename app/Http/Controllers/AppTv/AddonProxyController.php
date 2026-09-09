<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\YamboAddonToken;
use App\Support\YamboSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Proxy del addon de streams premium bajo nuestro dominio:
 *
 *   https://tv.yammbo.com/aio/{token}/manifest.json
 *
 * Qué resuelve, en concreto:
 *  - La URL real del proveedor ya no viaja al cliente, así que el diálogo
 *    "Compartir complemento" (Facebook / X / Reddit / Copiar) deja de repartirla.
 *  - Cada cuenta tiene su token, revocable. Si una URL se filtra se corta esa
 *    y sólo esa.
 *  - Sin suscripción activa el manifest devuelve 403, así que la copia que
 *    alguien se guardó deja de servir en cuanto cancela.
 *
 * Lo que NO resuelve, para que quede dicho: las URLs de reproducción que
 * devuelve el proveedor siguen apuntando a su host. Proxear vídeo por el VPS
 * costaría el ancho de banda de todos los usuarios; el objetivo aquí es el
 * manifest, que es lo instalable y lo que se comparte de un clic.
 */
class AddonProxyController extends Controller
{
    /** Rutas del proveedor que nunca se exponen (su panel de configuración). */
    private const BLOCKED = ['configure', 'api'];

    public function handle(Request $request, string $token, string $path = 'manifest.json')
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->cors(response('', 204));
        }

        $upstreamBase = rtrim((string) config('yammbo.aio.base'), '/');
        if ($upstreamBase === '') {
            Log::error('[aio-proxy] yammbo.aio.base sin configurar');

            return $this->cors(response()->json(['err' => 'Addon not configured'], 503));
        }

        $path = ltrim($path, '/');
        if ($path === '') {
            $path = 'manifest.json';
        }

        // Delimitador `#`: con `~` el propio `~` del juego de caracteres cerraba
        // el patrón y preg_match reventaba con "Unknown modifier", así que toda
        // petición al proxy daba 500.
        if (str_contains($path, '..') || ! preg_match('#^[A-Za-z0-9._~/%+=:,@()!*\[\]-]+$#', $path)) {
            return $this->cors(response()->json(['err' => 'Bad request'], 400));
        }

        $first = explode('/', $path)[0];
        if (in_array($first, self::BLOCKED, true)) {
            return $this->cors(response()->json(['err' => 'Not found'], 404));
        }

        $row = YamboAddonToken::where('token', $token)->whereNull('revoked_at')->first();
        if (! $row) {
            return $this->cors(response()->json(['err' => 'Invalid or revoked addon token'], 403));
        }

        $user = User::find($row->user_id);
        if (! $user || ! YamboSubscription::isActive($user)) {
            return $this->cors(response()->json(['err' => 'Subscription is not active'], 403));
        }

        $this->track($row, $request);

        $url = $upstreamBase.'/'.$path;
        $query = $request->getQueryString();
        if ($query) {
            $url .= '?'.$query;
        }

        try {
            $res = Http::withHeaders([
                'Accept' => $request->header('Accept', '*/*'),
                'User-Agent' => 'YammboTv/1.0 (+https://tv.yammbo.com)',
            ])->timeout(20)->get($url);
        } catch (\Throwable $e) {
            Log::warning('[aio-proxy] upstream falló: '.$e->getMessage(), ['path' => $path]);

            return $this->cors(response()->json(['err' => 'Upstream unavailable'], 502));
        }

        if (str_ends_with($path, 'manifest.json') && $res->successful()) {
            $manifest = $res->json();
            if (is_array($manifest)) {
                return $this->cors(response()->json(
                    $this->brandManifest($manifest, $request),
                    200,
                    [],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ));
            }
        }

        return $this->cors(response(
            $res->body(),
            $res->status(),
            ['Content-Type' => $res->header('Content-Type') ?: 'application/json']
        ));
    }

    /**
     * Deja el manifest con nuestra marca. Además de ocultar de dónde sale,
     * `configurable => false` quita el botón del engranaje en /app/#/addons,
     * que abría el panel del proveedor.
     */
    private function brandManifest(array $manifest, Request $request): array
    {
        $spanish = ! str_starts_with(strtolower((string) $request->header('Accept-Language', 'es')), 'en');

        $manifest['id'] = 'com.yammbo.premium';
        $manifest['name'] = 'Yammbo Premium';
        $manifest['description'] = $spanish
            ? 'Fuentes de reproducción en alta calidad incluidas con tu suscripción a Yammbo Tv. Se activa y se desactiva solo según tu plan.'
            : 'High quality playback sources included with your Yammbo Tv subscription. Enabled and disabled automatically with your plan.';
        $manifest['logo'] = url('/images/yambo-icon.png');

        unset(
            $manifest['contactEmail'],
            $manifest['stremioAddonsConfig'],
            $manifest['config'],
            $manifest['background']
        );

        $hints = is_array($manifest['behaviorHints'] ?? null) ? $manifest['behaviorHints'] : [];
        $hints['configurable'] = false;
        $hints['configurationRequired'] = false;
        $manifest['behaviorHints'] = $hints;

        return $manifest;
    }

    /**
     * Registra uso y cuenta IPs distintas del día (hasheadas, no en claro).
     * Sólo avisa por el log: cortar automáticamente convertiría a una familia
     * con varios dispositivos en un falso positivo, y un usuario legítimo
     * bloqueado cuesta más que un gorrón colado.
     */
    private function track(YamboAddonToken $row, Request $request): void
    {
        $today = Carbon::now()->toDateString();
        $hash = substr(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')), 0, 16);

        $ips = is_array($row->ips) ? $row->ips : [];
        $seenToday = $ips[$today] ?? [];
        $isNewIp = ! in_array($hash, $seenToday, true);
        $todayIps = $isNewIp ? array_merge($seenToday, [$hash]) : $seenToday;

        $stale = $row->last_used_at === null || $row->last_used_at->lt(Carbon::now()->subMinute());
        if (! $isNewIp && ! $stale) {
            return;
        }

        // Nivel error a propósito: el .env tiene LOG_LEVEL=error, así que un
        // warning aquí no se escribiría en ningún sitio y la única señal de que
        // una URL está circulando se perdería.
        $max = (int) config('yammbo.aio.max_ips_per_day', 8);
        if ($isNewIp && count($todayIps) > $max) {
            Log::error('[aio-proxy] token con muchas IPs distintas hoy', [
                'user_id' => $row->user_id,
                'distinct_ips' => count($todayIps),
                'max' => $max,
            ]);
        }

        $row->forceFill([
            'ips' => [$today => array_slice($todayIps, -50)],
            'last_used_at' => Carbon::now(),
            'hits' => $row->hits + 1,
        ])->save();
    }

    private function cors($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Cache-Control', 'no-store');
    }
}

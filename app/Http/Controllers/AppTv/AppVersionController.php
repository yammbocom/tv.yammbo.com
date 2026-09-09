<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Version publicada de la app de TELEVISORES (Android TV, com.yammbo.tv).
 *
 * OJO: esto no tiene nada que ver con la app movil; es la de televisores.
 *
 * La app consulta este endpoint al abrir y, si el "build" de aqui es mayor que
 * el suyo, ofrece actualizar.
 *
 * Origen de los datos (storage/app/tv-version.json):
 *
 *   {"source": "github"}            -> lee el ultimo release de
 *                                      github.com/yammbocom/yammbo-androidtv-releases
 *                                      Publicar = crear el release. Nada mas.
 *
 *   {"build": 30, "url": "...", ...} -> valores fijos (modo manual, sirve de
 *                                      respaldo si GitHub falla).
 *
 * Del release de GitHub se saca:
 *   - build        <- del tag: "b31" o "31" -> 31
 *   - version_name <- del nombre del release, o el propio tag
 *   - notes        <- del cuerpo del release
 *   - url + size   <- del primer asset .apk
 */
class AppVersionController extends Controller
{
    private const FILE  = 'tv-version.json';
    private const REPO  = 'yammbocom/yammbo-androidtv-releases';
    private const CACHE_KEY = 'tv_version_github';
    private const CACHE_MIN = 10;

    public function __invoke(Request $request): JsonResponse
    {
        $default = [
            'build'        => 0,
            'version_code' => 0,
            'version_name' => '',
            'url'          => '',
            'size'         => 0,
            'sha256'       => '',
            'notes'        => '',
            'mandatory'    => false,
        ];

        $local = [];
        try {
            $path = storage_path('app/' . self::FILE);
            if (is_file($path)) {
                $decoded = json_decode((string) file_get_contents($path), true);
                if (is_array($decoded)) {
                    $local = $decoded;
                }
            }
        } catch (\Throwable $e) {
            // se sigue con los valores por defecto
        }

        // Modo GitHub: el ultimo release manda
        if (($local['source'] ?? '') === 'github') {
            $fromGithub = $this->fromGithub();
            if ($fromGithub) {
                // mandatory y sha256 se pueden forzar desde el fichero local
                $out = array_merge($default, $fromGithub);
                if (array_key_exists('mandatory', $local)) {
                    $out['mandatory'] = (bool) $local['mandatory'];
                }
                if (! empty($local['sha256'])) {
                    $out['sha256'] = (string) $local['sha256'];
                }
                $out['version_code'] = (int) ($local['version_code'] ?? 0);
                return response()->json($out);
            }
            // Si GitHub falla, se cae al modo manual con lo que haya en el fichero
        }

        $out = array_merge($default, $local);
        unset($out['source']);
        $out['build']        = (int) $out['build'];
        $out['version_code'] = (int) $out['version_code'];
        $out['size']         = (int) $out['size'];
        $out['mandatory']    = (bool) $out['mandatory'];

        return response()->json($out);
    }

    /** Ultimo release publicado, cacheado para no llamar a GitHub en cada arranque. */
    private function fromGithub(): ?array
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_MIN), function () {
                $ch = curl_init('https://api.github.com/repos/' . self::REPO . '/releases/latest');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_HTTPHEADER     => [
                        'Accept: application/vnd.github+json',
                        'User-Agent: YamboTV-Server',
                    ],
                ]);
                $body = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code !== 200 || ! $body) {
                    return null;
                }

                $r = json_decode($body, true);
                if (! is_array($r)) {
                    return null;
                }

                // Asset: el primer .apk del release
                $url = '';
                $size = 0;
                foreach (($r['assets'] ?? []) as $a) {
                    if (str_ends_with(strtolower($a['name'] ?? ''), '.apk')) {
                        $url  = (string) ($a['browser_download_url'] ?? '');
                        $size = (int) ($a['size'] ?? 0);
                        break;
                    }
                }
                if ($url === '') {
                    return null;   // release sin APK: no ofrecer nada
                }

                $tag = (string) ($r['tag_name'] ?? '');
                $build = (int) preg_replace('/\D/', '', $tag);

                return [
                    'build'        => $build,
                    'version_name' => trim((string) ($r['name'] ?? '')) ?: $tag,
                    'url'          => $url,
                    'size'         => $size,
                    'notes'        => trim((string) ($r['body'] ?? '')),
                    'sha256'       => '',
                    'mandatory'    => false,
                ];
            });
        } catch (\Throwable $e) {
            return null;
        }
    }
}

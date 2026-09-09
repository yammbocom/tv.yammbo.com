<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Version publicada de la app MOVIL (com.msandroid.mobileNF).
 *
 * Es un canal SEPARADO del de televisores (AppVersionController): la app movil
 * consulta este endpoint al abrir y, si el "build" de aqui es mayor que el suyo,
 * ofrece actualizar descargando el APK de la url indicada.
 *
 * Datos en storage/app/mobile-version.json (modo manual, para no mezclarse con
 * los releases viejos que comparten el repo):
 *
 *   {"build": 20, "version_name": "v20", "url": "https://github.com/.../YammboMobile.apk",
 *    "size": 46600000, "sha256": "", "notes": "...", "mandatory": false}
 */
class MobileVersionController extends Controller
{
    private const FILE = 'mobile-version.json';

    public function __invoke(): JsonResponse
    {
        $out = [
            'build'        => 0,
            'version_code' => 0,
            'version_name' => '',
            'url'          => '',
            'size'         => 0,
            'sha256'       => '',
            'notes'        => '',
            'mandatory'    => false,
        ];

        try {
            $path = storage_path('app/' . self::FILE);
            if (is_file($path)) {
                $decoded = json_decode((string) file_get_contents($path), true);
                if (is_array($decoded)) {
                    $out = array_merge($out, $decoded);
                }
            }
        } catch (\Throwable $e) {
            // valores por defecto
        }

        $out['build']     = (int) $out['build'];
        $out['size']      = (int) $out['size'];
        $out['mandatory'] = (bool) $out['mandatory'];

        return response()->json($out);
    }
}

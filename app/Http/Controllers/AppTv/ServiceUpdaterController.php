<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Auto-actualización del Yammbo TV Service (el .exe de PC).
 *
 * El servicio (fork de stremio-service, Rust) hace dos peticiones al arrancar:
 *
 *   1) GET /updater/check?product=yammbo-tv-service
 *      -> {"versionDesc": "<url del descriptor>", "version": "0.1.19"}
 *
 *   2) GET <versionDesc>
 *      -> {"version": "0.1.19", "files": [{"url": ..., "checksum": "<sha256>", "os": "windows"}]}
 *
 * Después descarga el instalador, **verifica el SHA256** y lo ejecuta en
 * silencio (`/SILENT /NOCANCEL /FORCECLOSEAPPLICATIONS /TASKS=runapp`).
 * No hay firma criptográfica en el protocolo: el checksum es la única garantía
 * de integridad, por eso se calcula aquí sobre el fichero real y nunca se
 * copia a mano.
 *
 * Reglas del cliente que condicionan este código:
 *  - Solo actualiza si la versión anunciada es ESTRICTAMENTE MAYOR que la suya.
 *  - Si `version` de (1) y (2) no coinciden, aborta con "Mismatched update versions".
 *  - Busca el fichero cuyo `os` coincide con `std::env::consts::OS`.
 *
 * Ante cualquier duda devolvemos 404 (= "no hay actualización"): que un cliente
 * no se actualice es un problema menor; que se descargue un instalador que no
 * existe o roto, no.
 */
class ServiceUpdaterController extends Controller
{
    public function check(Request $request)
    {
        $product = (string) $request->query('product', '');
        if ($product !== '' && $product !== config('yammbo_service.product')) {
            return response()->json(['err' => 'Unknown product'], 404);
        }

        $version = $this->version();
        if ($this->resolveFiles($version) === []) {
            // Sin instalador servible no anunciamos nada.
            return response()->json(['err' => 'No update available'], 404);
        }

        return response()->json([
            'versionDesc' => url('/updater/descriptor/'.$version.'.json'),
            'version' => $version,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function descriptor(string $version)
    {
        // El cliente compara esta versión con la de /check y aborta si difieren,
        // así que solo servimos el descriptor de la versión vigente.
        if ($version !== $this->version()) {
            return response()->json(['err' => 'Unknown version'], 404);
        }

        $files = $this->resolveFiles($version);
        if ($files === []) {
            return response()->json(['err' => 'No files for this version'], 404);
        }

        return response()->json([
            'version' => $version,
            'files' => $files,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    private function version(): string
    {
        return (string) config('yammbo_service.version', '0.0.0');
    }

    /**
     * Instaladores realmente servibles, con el SHA256 calculado sobre el fichero
     * en disco. Si el fichero no está, esa entrada NO se anuncia.
     *
     * @return array<int, array{url: string, checksum: string, os: string}>
     */
    private function resolveFiles(string $version): array
    {
        $files = [];

        foreach ((array) config('yammbo_service.installers', []) as $os => $relative) {
            $path = public_path((string) $relative);

            if (! is_file($path) || ! is_readable($path)) {
                Log::error('[service-updater] instalador declarado pero ausente', [
                    'os' => $os,
                    'version' => $version,
                    'path' => $relative,
                ]);

                continue;
            }

            $files[] = [
                'url' => url('/'.ltrim((string) $relative, '/')),
                'checksum' => $this->sha256($path),
                'os' => (string) $os,
            ];
        }

        return $files;
    }

    /**
     * SHA256 cacheado. Son ~37 MB: hashearlo en cada petición quemaría CPU sin
     * motivo. La clave lleva tamaño y mtime, así que al subir un instalador
     * nuevo el hash se recalcula solo — no hay que acordarse de purgar nada.
     */
    private function sha256(string $path): string
    {
        $key = 'yambo:service-sha256:'.md5($path).':'.filesize($path).':'.filemtime($path);

        return Cache::remember($key, 30 * 24 * 3600, static fn () => hash_file('sha256', $path));
    }
}

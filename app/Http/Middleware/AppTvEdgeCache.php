<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja que Cloudflare guarde las respuestas de la API de la app que son iguales
 * para todos (versión, tráileres, fichas, avatares, podcasts). Con la Cache Rule
 * de la zona, la segunda petición igual la contesta el edge y no llega al VPS.
 *
 * Solo se marca como cacheable lo que es seguro:
 * - GET/HEAD con 200, nunca un error, un 503 de "building" ni un 429;
 * - sin token del usuario (?t=, X-App-Token, Authorization);
 * - sin respuestas vacías o con ok:false (un fallo de TMDB o YouTube no se
 *   queda pegado en el edge un día entero).
 */
class AppTvEdgeCache
{
    /** path (sin barra inicial) => segundos en el edge (s-maxage). */
    private const EXACT = [
        'api/app-tv/version'         => 300,
        'api/app-tv/version-movil'   => 300,
        'api/app-tv/trailer'         => 86400,
        'api/app-tv/ficha'           => 86400,
        'api/app-tv/avatars'         => 86400,
        'api/app-tv/podcasts/home'   => 1800,
        'api/app-tv/podcasts/search' => 2700,  // portadas firmadas: el origen tampoco pasa de 45 min
    ];

    private const PREFIX = [
        'api/app-tv/podcasts/show/' => 3600,
    ];

    /** Claves de lista que, vacías, indican que no hubo datos. */
    private const LIST_KEYS = ['rows', 'items', 'avatars'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $ttl = $this->ttlFor($request->path());
        if ($ttl === null
            || ! $request->isMethodCacheable()
            || $response->getStatusCode() !== 200
            || $request->query->has('t')
            || $request->headers->has('X-App-Token')
            || $request->headers->has('Authorization')
            || ! $this->isUsefulBody($response)) {
            return $response;
        }

        $response->headers->remove('Set-Cookie');
        $response->headers->set('Cache-Control', "public, max-age=60, s-maxage={$ttl}");

        return $response;
    }

    private function ttlFor(string $path): ?int
    {
        if (isset(self::EXACT[$path])) {
            return self::EXACT[$path];
        }
        foreach (self::PREFIX as $prefix => $ttl) {
            if (str_starts_with($path, $prefix)) {
                return $ttl;
            }
        }

        return null;
    }

    private function isUsefulBody(Response $response): bool
    {
        $data = json_decode((string) $response->getContent(), true);
        if (! is_array($data) || $data === []) {
            return false;
        }
        if (array_key_exists('ok', $data) && $data['ok'] === false) {
            return false;
        }
        foreach (self::LIST_KEYS as $key) {
            if (array_key_exists($key, $data) && empty($data[$key])) {
                return false;
            }
        }

        return true;
    }
}

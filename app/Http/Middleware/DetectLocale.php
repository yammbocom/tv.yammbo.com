<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detecta el idioma preferido del usuario y lo aplica a toda la app.
 *
 * Prioridad (primera coincidencia gana):
 *   1. ?lang=es|en en la query string (y la persiste en cookie 'yambo_lang' por 1 año)
 *   2. Cookie 'yambo_lang' previamente establecida
 *   3. auth()->user()->language o profile_key 'language' si existe
 *   4. Accept-Language header (prefijo es* → es, resto → en)
 *   5. Fallback config('app.locale')
 *
 * Idiomas soportados: es, en (extensible).
 */
class DetectLocale
{
    private const SUPPORTED = ['es', 'en'];

    private const COOKIE = 'yambo_lang';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        // devdojo/auth lee sus textos de config en cada render; en español se
        // sustituyen por los de language_es.php (language.php queda como base).
        if ($locale === 'es' && is_array(config('devdojo.auth.language_es'))) {
            config(['devdojo.auth.language' => array_replace_recursive(
                (array) config('devdojo.auth.language'),
                config('devdojo.auth.language_es')
            )]);
        }

        $response = $next($request);

        // Persiste la elección del query param en cookie para futuras visitas
        if ($request->query('lang') && in_array($request->query('lang'), self::SUPPORTED, true)) {
            $cookie = Cookie::make(self::COOKIE, $locale, 60 * 24 * 365);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Query override
        $q = $request->query('lang');
        if (is_string($q) && in_array($q, self::SUPPORTED, true)) {
            return $q;
        }

        // 2. Cookie
        $c = $request->cookie(self::COOKIE);
        if (is_string($c) && in_array($c, self::SUPPORTED, true)) {
            return $c;
        }

        // 3. Auth user preference (si existe columna language)
        $user = $request->user();
        if ($user && isset($user->language) && in_array($user->language, self::SUPPORTED, true)) {
            return $user->language;
        }

        // 4. Accept-Language header
        $al = (string) $request->header('Accept-Language', '');
        if ($al !== '') {
            $primary = strtolower(substr($al, 0, 2));
            if (in_array($primary, self::SUPPORTED, true)) {
                return $primary;
            }
        }

        // 5. Fallback
        return (string) config('app.locale', 'en');
    }
}

<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Lab404\Impersonate\ImpersonateServiceProvider::class,
        \Wave\WaveServiceProvider::class,
        \DevDojo\Themes\ThemesServiceProvider::class,
        \DevDojo\Themes\ThemesServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cloudflare va por delante: sin esto la IP del cliente es la del edge
        // (distinta en cada petición) y ningún throttle por IP llega a saltar.
        $middleware->trustProxies(
            at: require __DIR__.'/../config/cloudflare-proxies.php',
            // Sin X_FORWARDED_HOST a propósito: Cloudflare reenvía la cabecera
            // tal cual la manda el cliente, así que confiar en ella deja que
            // cualquiera fije el host de la petición. Con eso, un POST a
            // /forgot-password genera el enlace de reseteo apuntando al dominio
            // del atacante y el correo sale legítimo desde aquí.
            headers: Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->encryptCookies(except: [
            'theme',
        ]);

        // Yammbo Tv: detect user locale from ?lang, cookie, Accept-Language
        $middleware->web(append: [
            \App\Http\Middleware\DetectLocale::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/webhook/paddle',
            '/webhook/stripe',
        ]);

        $middleware->append(\Filament\Http\Middleware\DisableBladeIconComponents::class);

        $middleware->web(\RalphJSmit\Livewire\Urls\Middleware\LivewireUrlsMiddleware::class);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

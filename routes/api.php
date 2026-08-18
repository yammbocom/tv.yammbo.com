<?php

use App\Http\Controllers\AppTv\AppTvAuthController;
use App\Http\Controllers\AppTv\AppTvLibraryController;
use App\Http\Controllers\AppTv\AppTvStatusController;
use App\Http\Controllers\AppTv\PortalCoreController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return auth()->user();
});

/*
|--------------------------------------------------------------------------
| YamboTV APK — stateless JSON endpoints
|--------------------------------------------------------------------------
| Consumidos por NativeAuthAty + ActivateAty$StatusTask. Sin CSRF (API puro).
| Login por email/password; devuelve un JWT que el cliente debe reenviar como
| `Authorization: Bearer` en los endpoints que exponen datos de la cuenta.
*/

// Públicas: emiten credenciales o no revelan datos de una cuenta concreta.
// login/register van con throttle propio: son el punto de fuerza bruta.
Route::prefix('app-tv')->group(function () {
    Route::post('login', [AppTvAuthController::class, 'login'])->middleware('throttle:8,1');
    Route::post('register', [AppTvAuthController::class, 'register'])->middleware('throttle:5,10');

    // TV-LINK-QR-API-V54
    Route::post('tv-link/generate', [\App\Http\Controllers\AppTv\TvLinkController::class, 'generate'])->middleware('throttle:10,1');
    Route::post('tv-link/poll', [\App\Http\Controllers\AppTv\TvLinkController::class, 'poll'])->middleware('throttle:30,1');
});

// Privadas: el dueño sale de la sesión web o del JWT, nunca del request.
// StartSession es necesario porque el grupo `api` no arranca sesión y la SPA
// de /app se identifica con la cookie (fetch con credentials: 'same-origin').
Route::prefix('app-tv')
    ->middleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \App\Http\Middleware\ResolveAppTvUser::class,
    ])
    ->group(function () {
        Route::get('status', AppTvStatusController::class);

        // Yambo Library + Calendar (independiente de Stremio Cloud)
        Route::get('library', [AppTvLibraryController::class, 'index']);
        Route::get('library/status', [AppTvLibraryController::class, 'status']);
        Route::post('library/toggle', [AppTvLibraryController::class, 'toggle']);
        Route::post('library/progress', [AppTvLibraryController::class, 'progress']);
        Route::post('library/sync-episodes', [AppTvLibraryController::class, 'syncEpisodes']);
        Route::get('calendar', [AppTvLibraryController::class, 'calendar']);
    });

/*
|--------------------------------------------------------------------------
| Mock portal API legacy (XuperTV) — para que el APK YamboTV cargue
|--------------------------------------------------------------------------
| El APK llama /api/portalCore/v8/active al startup; sin esto se queda en
| pantalla de carga porque el wrapper RxJava espera ActiveResult exitoso.
| Catch-all loguea otros endpoints portalCore que el APK pida — útil para
| iterar si la app pide más endpoints.
*/
Route::post('portalCore/v8/active', [PortalCoreController::class, 'active']);
Route::any('portalCore/{path?}', [PortalCoreController::class, 'fallback'])
    ->where('path', '.*');

// Wave default API (/api/login, /api/register, /api/logout, /api/refresh, /api/token)
Wave::api();

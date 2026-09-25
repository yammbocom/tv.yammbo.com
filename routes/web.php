<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AppTv\AppTvForgotPasswordController;
use App\Http\Controllers\AppTv\AppTvPaymentController;
use App\Http\Controllers\AppTv\AppTvSubscriptionController;
use App\Http\Controllers\AppTv\BillingController;
use App\Http\Controllers\StremioAddonController;
use Illuminate\Support\Facades\Route;

// Stremio addon endpoints (mounted at root so manifest URL is https://tv.yammbo.com/manifest.json)
Route::get('/manifest.json', [StremioAddonController::class, 'manifest']);
Route::get('/catalog/{type}/{id}.json', [StremioAddonController::class, 'catalog']);
Route::get('/catalog/{type}/{id}/{extra}.json', [StremioAddonController::class, 'catalog']);
Route::get('/meta/{type}/{id}.json', [StremioAddonController::class, 'meta']);
Route::get('/stream/{type}/{id}.json', [StremioAddonController::class, 'stream']);

/*
 * Auto-actualizacion del Yammbo TV Service (el .exe de PC). El servicio pide
 * /updater/check al arrancar y, si hay version mayor, descarga el instalador
 * del descriptor, verifica su SHA256 y lo ejecuta en silencio.
 *
 * Publicas: el servicio corre sin sesion. Con throttle porque el endpoint
 * abre un fichero de 37 MB para hashearlo (aunque va cacheado).
 */
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/updater/check', [\App\Http\Controllers\AppTv\ServiceUpdaterController::class, 'check']);
    Route::get('/updater/descriptor/{version}.json', [\App\Http\Controllers\AppTv\ServiceUpdaterController::class, 'descriptor'])
        ->where('version', '[0-9]+\.[0-9]+\.[0-9]+');
});

/*
 * Catalogo de complementos propio. Mismo formato `addon_catalog` que consume
 * stremio-core, pero la lista la elegimos nosotros (config/yammbo_addons.php)
 * y los logos salen de nuestro dominio en vez de 53 hosts de terceros.
 */
Route::get('/addon_catalog/{type}/{id}.json', [\App\Http\Controllers\AppTv\AddonCatalogController::class, 'catalog']);
Route::get('/addon-logo/{slug}', [\App\Http\Controllers\AppTv\AddonCatalogController::class, 'logo'])
    ->where('slug', '[a-z0-9\-]+');

/*
 * Proxy del addon de streams premium. Un token por cuenta, revocable, y 403
 * en cuanto la suscripcion deja de estar activa.
 */
Route::match(['get', 'options'], '/aio/{token}/{path}', [\App\Http\Controllers\AppTv\AddonProxyController::class, 'handle'])
    ->where(['token' => '[a-z0-9]{32,64}', 'path' => '.*']);

/**
 * Stremio SPA (/app) — sirve index.html con el user Wave/Laravel inyectado
 * como window.YAMBO_USER para que el NavMenu y User.tsx muestren el email
 * real en vez de "Anonymous user".
 */
/**
 * Helper: check if user has an active subscription (Wave Subscription or trial).
 * Returns ['active' => bool, 'plan' => string|null].
 */
$yamboSubscriptionFor = function ($user) {
    if (! $user) {
        return ['active' => false, 'plan' => null];
    }
    $now = \Carbon\Carbon::now();
    $sub = \App\Models\Subscription::where('billable_type', 'user')
        ->where('billable_id', $user->id)
        ->whereIn('status', ['active', 'trialing'])
        ->where(function ($q) use ($now) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
        })
        ->orderByDesc('id')
        ->first();
    if ($sub) {
        $plan = \App\Models\Plan::find($sub->plan_id);

        return ['active' => true, 'plan' => $plan->name ?? 'Premium'];
    }
    if ($user->trial_ends_at && \Carbon\Carbon::parse($user->trial_ends_at)->isFuture()) {
        return ['active' => true, 'plan' => 'Trial'];
    }

    return ['active' => false, 'plan' => null];
};

$serveStremioApp = function () use ($yamboSubscriptionFor) {
    $html = @file_get_contents(public_path('app/index.html'));
    if ($html === false) {
        abort(500, 'Stremio SPA not deployed');
    }

    $user = auth()->user();
    $sub = $yamboSubscriptionFor($user);
    $payload = $user ? [
        'id' => (int) $user->id,
        'email' => (string) $user->email,
        'name' => (string) ($user->name ?? ''),
        'locale' => app()->getLocale(),
        'subscription_active' => (bool) $sub['active'],
        'plan' => $sub['plan'],
    ] : null;

    // Hash del build vigente, para que el cliente tire un service worker viejo.
    // Sale del propio index.html (única fuente de verdad de lo que se sirve): con
    // glob() se colaba el primer hash alfabético, que al acumularse builds viejos
    // era uno obsoleto, y entonces el guard nunca detectaba el cambio de versión.
    $buildHash = '';
    if (preg_match('~([a-f0-9]{40})/scripts/main\.js~', $html, $m)) {
        $buildHash = $m[1];
    }

    $jsonUser = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $swGuard = 'if("serviceWorker" in navigator){navigator.serviceWorker.getRegistrations().then(function(rs){rs.forEach(function(r){if(r.active){r.update();}});});var YAMBO_SW_KEY="yambo_sw_build";var prev=localStorage.getItem(YAMBO_SW_KEY);if(prev && prev!==window.YAMBO_BUILD){navigator.serviceWorker.getRegistrations().then(function(rs){Promise.all(rs.map(function(r){return r.unregister();})).then(function(){localStorage.setItem(YAMBO_SW_KEY,window.YAMBO_BUILD);location.reload();});});}else{localStorage.setItem(YAMBO_SW_KEY,window.YAMBO_BUILD);}}';
    $inject = '<script>window.YAMBO_USER = '.$jsonUser.';window.YAMBO_BUILD = "'.$buildHash.'";'.$swGuard.'</script>';
    $html = str_replace('</head>', $inject.'</head>', $html);

    return response($html, 200, ['Content-Type' => 'text/html']);
};

Route::get('/app', $serveStremioApp)->middleware('auth');
Route::get('/app/', $serveStremioApp)->middleware(['auth', \App\Http\Middleware\EnsureEmailVerified::class]);

// Yambo user identity endpoint for Stremio SPA — fallback when window.YAMBO_USER
// is not injected (stale SW cache). Stateful session via web middleware.
Route::get('/api/app-tv/whoami', function () use ($yamboSubscriptionFor) {
    $user = auth()->user();
    if (! $user) {
        return response()->json(['user' => null], 200);
    }
    $sub = $yamboSubscriptionFor($user);

    return response()->json([
        'user' => [
            'id' => (int) $user->id,
            'email' => (string) $user->email,
            'name' => (string) ($user->name ?? ''),
            'locale' => app()->getLocale(),
            'subscription_active' => (bool) $sub['active'],
            'plan' => $sub['plan'],
        ],
    ]);
});

/*
 * URL del addon premium para el SPA. Se devuelve ya tokenizada para esta
 * cuenta; el cliente nunca ve la del proveedor.
 */
Route::get('/api/app-tv/addon-url', function () use ($yamboSubscriptionFor) {
    $user = auth()->user();
    if (! $user) {
        return response()->json(['active' => false, 'url' => null]);
    }

    if (! $yamboSubscriptionFor($user)['active']) {
        return response()->json(['active' => false, 'url' => null]);
    }

    $row = \App\Models\YamboAddonToken::forUser((int) $user->id);

    return response()->json([
        'active' => true,
        'url' => url('/aio/'.$row->token.'/manifest.json'),
    ]);
});

// /app/<path> sin el hash → redirigir con hash (Stremio usa HashRouter)
Route::get('/app/{path}', function ($path) {
    // Excluir assets reales (build con SHA-hash prefix) — Apache los sirve directo, no Laravel.
    if (preg_match('/^[a-f0-9]{40}/', $path) || preg_match('/^(images|fonts|favicons|screenshots|manifest\.json|service-worker)/', $path)) {
        abort(404);
    }

    return redirect('/app/#/'.$path);
})->where('path', '.*')->middleware('auth');

/*
|--------------------------------------------------------------------------
| YamboTV APK endpoints (tv.yammbo.com — unified backend post api.yammbo.com)
|--------------------------------------------------------------------------
| Rutas cargadas en el WebView de la APK. Necesitan el middleware `web`
| (session + CSRF) para que el botón de checkout pueda usar X-CSRF-TOKEN.
*/

// Estas tres tomaban user_id del request igual que las de routes/api.php, así
// que quedaban fuera del cierre del IDOR: /app-tv/subscription?user_id=N
// renderizaba el plan y la fecha de renovación de cualquier cuenta.
Route::middleware(\App\Http\Middleware\ResolveAppTvUser::class)->group(function () {
    Route::get('/app-tv/subscription', [AppTvSubscriptionController::class, 'show'])->name('app-tv.subscription');
    Route::get('/app-tv/payment-success', [AppTvPaymentController::class, 'success'])->name('app-tv.payment-success');

    // Checkout vive en /api/app-tv/checkout pero usa web middleware (session+CSRF) porque
    // lo dispara un fetch desde el blade subscription.
    Route::post('/api/app-tv/checkout', [AppTvSubscriptionController::class, 'checkout'])->name('app-tv.checkout');
});

// Páginas estáticas del WebView APK (Centro de Ayuda, Compartir, VPN info, Descarga)
// Servidas como vistas directas — sin lógica. download recibe la URL del APK.
Route::view('/app-tv/ayuda', 'app-tv.ayuda')->name('app-tv.ayuda');
Route::view('/app-tv/compartir', 'app-tv.compartir')->name('app-tv.compartir');
Route::view('/app-tv/vpn-info', 'app-tv.vpn-info')->name('app-tv.vpn-info');
// BlackListDialog del APK pide vpn-info.html — servimos la misma vista
Route::view('/app-tv/vpn-info.html', 'app-tv.vpn-info');

// === Ad container pages (hidden, only APK WebView can access) ===
// Gate: UA contains "YamboTV-Android" or "msandroid" OR ?apk=1 (dev). Otherwise 404.
$adGate = function ($view) {
    return function (\Illuminate\Http\Request $request) use ($view) {
        $ua = (string) $request->header('User-Agent', '');
        $isApk = str_contains($ua, 'YamboTV-Android')
              || str_contains($ua, 'msandroid')
              || $request->query('apk') === '1';
        if (! $isApk) { abort(404); }
        return response()->view($view, [
            'returnTo' => $request->query('return_to'),
            'contentId' => $request->query('content_id'),
        ]);
    };
};
Route::get('/app-tv/ads/banner', $adGate('app-tv.ads.banner'))->name('app-tv.ads.banner');
Route::get('/app-tv/download', function () {
    $apkPath = public_path('download/YamboTV.apk');
    $apkUrl = file_exists($apkPath)
        ? url('/download/YamboTV.apk').'?v='.filemtime($apkPath)
        : url('/download/YamboTV.apk');
    return view('app-tv.download', ['apkUrl' => $apkUrl]);
})->name('app-tv.download');

// Forgot password (WebView)
Route::get('/forgot-password', [AppTvForgotPasswordController::class, 'show'])->name('app-tv.forgot-password');
Route::post('/forgot-password', [AppTvForgotPasswordController::class, 'submit']);
Route::get('/auth/reset-password/{token}', [\App\Http\Controllers\AppTv\ResetPasswordController::class, 'show'])->middleware('web')->name('app-tv.reset-password');
Route::post('/auth/reset-password', [\App\Http\Controllers\AppTv\ResetPasswordController::class, 'reset'])->middleware('web');

// TV-LINK-QR-ROUTES-V54: QR login para APK Android TV (NativeAuthAty redirige al WebView aqui)
use App\Http\Controllers\AppTv\TvLinkController;
Route::get('/tv-link-app', function () {
    return view('tv-link-app');
})->name('app-tv.tv-link-app');
Route::get('/mi-cuenta-app', function () { return view('mi-cuenta-app'); })->name('app-tv.mi-cuenta-app');
Route::get('/acceso', function () { return view('acceso'); })->name('app-tv.acceso');
Route::get('/tv-link', [TvLinkController::class, 'show'])->name('app-tv.tv-link.show');
Route::post('/tv-link/confirm', [TvLinkController::class, 'confirm'])->name('app-tv.tv-link.confirm');
Route::post('/tv-link/register', [TvLinkController::class, 'register'])->name('app-tv.tv-link.register')->middleware('web');


/*
 * Mi Suscripción — gestión de plan (cancelar / cambiar / ver estado).
 * Disparado desde el menú user del SPA `/app` cuando yamboPremium = true.
 * Si user no tiene sub activa, redirige a /pricing.
 *
 * NOTA: ruta `/billing` ya está tomada por Wave (redirect catch-all). Por eso
 * usamos `/mi-suscripcion` que es libre y semánticamente claro en español.
 */
Route::get('/mi-suscripcion', [BillingController::class, 'show'])->name('billing.show');

// portal() y cancel() resolvían `user_id` del request cuando no había sesión, así
// que sin credencial alguna `?user_id=N` abría el Stripe Customer Portal de otra
// persona y cancelaba su suscripción. show() no lo hacía y se queda con su
// redirect a login, que es mejor UX que el 401 del middleware.
Route::middleware(\App\Http\Middleware\ResolveAppTvUser::class)->group(function () {
    Route::get('/mi-suscripcion/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/mi-suscripcion/cancelar', [BillingController::class, 'cancel'])->name('billing.cancel');
});

/*
 * Pricing direct checkout — desde el landing /pricing, los usuarios logged-in
 * van directo a Stripe Checkout (sin trial). Flujo propio (no Livewire) para
 * evitar la página /settings/subscription y la UX de Wave por defecto.
 */
Route::post('/pricing/checkout', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if (! $user) {
        return redirect('/auth/login?redirect='.urlencode('/pricing'));
    }

    $validated = $request->validate([
        'plan_id' => 'required|integer',
        'billing_cycle' => 'required|in:monthly,yearly',
    ]);

    $plan = \App\Models\Plan::where('id', $validated['plan_id'])->where('active', true)->first();
    if (! $plan) {
        abort(404, 'Plan no encontrado');
    }

    $priceId = $validated['billing_cycle'] === 'monthly'
        ? $plan->monthly_price_id
        : $plan->yearly_price_id;
    if (! $priceId) {
        abort(422, 'El plan no tiene precio configurado para ese ciclo');
    }

    $secretKey = config('yammbo.stripe.secret_key');
    if (! $secretKey) {
        abort(500, 'Stripe no configurado');
    }

    $stripe = new \Stripe\StripeClient($secretKey);
    $session = $stripe->checkout->sessions->create([
        'mode' => 'subscription',
        'line_items' => [[
            'price' => $priceId,
            'quantity' => 1,
        ]],
        'metadata' => [
            'billable_type' => 'user',
            'billable_id' => $user->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $validated['billing_cycle'],
        ],
        'client_reference_id' => (string) $user->id,
        'customer_email' => $user->email,
        'success_url' => url('/precios-tv/listo?src=web'),
        'cancel_url' => url('/pricing'),
    ]);

    return redirect()->away($session->url);
})->middleware('web')->name('pricing.checkout');


/*
 * Webhook propio. Va DESPUÉS de Wave::routes() a propósito: en una colisión de
 * método+URI gana la última ruta registrada, igual que el override de /install
 * de más abajo. Así sustituye al webhook de Wave sin tocar la URL registrada en
 * el dashboard de Stripe. La exclusión de CSRF de bootstrap/app.php es por URI,
 * así que sigue aplicando igual.
 */
Route::post('webhook/stripe', [\App\Http\Controllers\Billing\StripeWebhook::class, 'handler'])->name('webhook.stripe');

// Yambo: /install override (debe ir tras Wave::routes para ganar a wave.install)
Route::view("/install", "install");

/*
|--------------------------------------------------------------------------
| Panel de administración propio (/panel)
|--------------------------------------------------------------------------
| Sustituye a Filament (/admin) poco a poco: convive con el panel viejo
| hasta que quede verificado, así que nada de esto toca rutas ni vistas
| de Filament. Blade + controladores normales, sin Livewire/Folio.
*/
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PushController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsurePanelAdmin;

Route::prefix('panel')->middleware(['auth', EnsurePanelAdmin::class])->name('panel.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

    Route::get('/push', [PushController::class, 'index'])->name('push.index');
    Route::post('/push', [PushController::class, 'store'])->name('push.store');
});

// /admin era el panel de Filament. Se mantiene la URL viva porque es la que
// está memorizada y enlazada; ahora lleva al panel propio.
Route::redirect('/admin', '/panel');
Route::redirect('/admin/login', '/auth/login');

/*
 * Cierre de sesión en /logout.
 *
 * Lo servía Wave y se fue con el paquete, pero el bundle del SPA lleva
 * "/logout" compilado dentro: desde que se retiró, "Cerrar sesión" llevaba a
 * un 404 y dejaba la sesión abierta. Reconstruir el fork solo para cambiar esa
 * URL es mucho más caro que sostener la ruta aquí.
 *
 * Va por GET porque es lo que emite el SPA. Lo peor que consigue un tercero
 * incrustando la URL es cerrarle la sesión a alguien, y devdojo/auth ya expone
 * un GET equivalente en /auth/logout.
 */
Route::get('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
});  // sin ->name(): devdojo/auth ya registra el nombre "logout" para POST /auth/logout, y duplicarlo rompe route:cache

// TV-CHECKOUT: flujo de suscripcion desde la app de TV (QR del paywall).
// El token JWT identifica al usuario: el telefono no inicia sesion.
Route::get('/precios-tv', [\App\Http\Controllers\AppTv\TvCheckoutController::class, 'plans'])->middleware('web')->name('tv.precios');
Route::post('/precios-tv/checkout', [\App\Http\Controllers\AppTv\TvCheckoutController::class, 'checkout'])->middleware('web')->name('tv.precios.checkout');
Route::get('/precios-tv/listo', [\App\Http\Controllers\AppTv\TvCheckoutController::class, 'done'])->middleware('web')->name('tv.precios.listo');

// QR en PNG para pantallas nativas de la app de TV (Android no lee SVG).
Route::get('/qr.png', \App\Http\Controllers\AppTv\QrPngController::class)->name('tv.qr.png');

// Verificacion de correo (cuentas nuevas). Enlace firmado que caduca en 48h.
Route::get('/verificar-correo/{id}/{hash}', [\App\Http\Controllers\AppTv\EmailVerificationController::class, 'verify'])
    ->name('app-tv.verificar-correo')->middleware('web');
Route::post('/reenviar-verificacion', [\App\Http\Controllers\AppTv\EmailVerificationController::class, 'resend'])
    ->name('app-tv.reenviar-verificacion')->middleware(['web', 'throttle:5,10']);

// Aviso para quien aun no confirmo su correo (web).
Route::view('/verifica-tu-correo', 'verificar-aviso')->name('app-tv.verificar-aviso')->middleware('web');

// --- descargas-github ---------------------------------------------------
// Los APK ya no se alojan en el VPS: se sirven desde GitHub Releases.
// Estas rutas mantienen vivos los enlaces de siempre y dan URLs cortas.
// Las "latest" de GitHub no cambian al publicar una version nueva.
Route::get('/download/tv', function () {
    return redirect()->away('https://github.com/yammbocom/yammbo-androidtv-releases/releases/latest/download/YamboTV.apk');
})->name('download.tv');

Route::get('/download/movil', function () {
    return redirect()->away('https://github.com/yammbocom/YammboTv-APK-Releases/releases/latest/download/YammboMobile.apk');
})->name('download.movil');

// Alias en ingles y el enlace historico que ya circula por ahi
Route::get('/download/mobile', fn () => redirect()->route('download.movil'));
Route::get('/download/YamboTV.apk', fn () => redirect()->route('download.movil'));
Route::get('/download/YamboTV-TV.apk', fn () => redirect()->route('download.tv'));

// --- rutas-cortas-downloader --------------------------------------------
// Para escribirlas con el mando en la app Downloader (Fire TV / Android TV).
// Cuanto mas corta, menos teclea el usuario: tv.yammbo.com/tv
Route::get('/tv', fn () => redirect()->route('download.tv'));
// El boton de la app web mandaba aqui y el navegador se quedaba en una pestana en negro
// tras bajar el APK; /install explica cada dispositivo y tiene su propio boton.
Route::get('/apk', fn () => redirect('/install#movil'));
Route::get('/pc', fn () => redirect()->away(url(config('yammbo_service.installers.windows'))));






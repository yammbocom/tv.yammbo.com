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
use Wave\Facades\Wave;

// Stremio addon endpoints (mounted at root so manifest URL is https://tv.yammbo.com/manifest.json)
Route::get('/manifest.json', [StremioAddonController::class, 'manifest']);
Route::get('/catalog/{type}/{id}.json', [StremioAddonController::class, 'catalog']);
Route::get('/catalog/{type}/{id}/{extra}.json', [StremioAddonController::class, 'catalog']);
Route::get('/meta/{type}/{id}.json', [StremioAddonController::class, 'meta']);
Route::get('/stream/{type}/{id}.json', [StremioAddonController::class, 'stream']);

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
    $sub = \Wave\Subscription::where('billable_type', 'user')
        ->where('billable_id', $user->id)
        ->whereIn('status', ['active', 'trialing'])
        ->where(function ($q) use ($now) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
        })
        ->orderByDesc('id')
        ->first();
    if ($sub) {
        $plan = \Wave\Plan::find($sub->plan_id);

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
Route::get('/app/', $serveStremioApp)->middleware('auth');

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

// TV-LINK-QR-ROUTES-V54: QR login para APK Android TV (NativeAuthAty redirige al WebView aqui)
use App\Http\Controllers\AppTv\TvLinkController;
Route::get('/tv-link-app', function () {
    return view('tv-link-app');
})->name('app-tv.tv-link-app');
Route::get('/tv-link', [TvLinkController::class, 'show'])->name('app-tv.tv-link.show');
Route::post('/tv-link/confirm', [TvLinkController::class, 'confirm'])->name('app-tv.tv-link.confirm');


/*
 * Mi Suscripción — gestión de plan (cancelar / cambiar / ver estado).
 * Disparado desde el menú user del SPA `/app` cuando yamboPremium = true.
 * Si user no tiene sub activa, redirige a /pricing.
 *
 * NOTA: ruta `/billing` ya está tomada por Wave (redirect catch-all). Por eso
 * usamos `/mi-suscripcion` que es libre y semánticamente claro en español.
 */
Route::get('/mi-suscripcion', [BillingController::class, 'show'])->name('billing.show');
Route::get('/mi-suscripcion/portal', [BillingController::class, 'portal'])->name('billing.portal');
Route::post('/mi-suscripcion/cancelar', [BillingController::class, 'cancel'])->name('billing.cancel');

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

    $plan = \Wave\Plan::where('id', $validated['plan_id'])->where('active', true)->first();
    if (! $plan) {
        abort(404, 'Plan no encontrado');
    }

    $priceId = $validated['billing_cycle'] === 'monthly'
        ? $plan->monthly_price_id
        : $plan->yearly_price_id;
    if (! $priceId) {
        abort(422, 'El plan no tiene precio configurado para ese ciclo');
    }

    $secretKey = config('wave.stripe.secret_key');
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
        'success_url' => url('/app'),
        'cancel_url' => url('/pricing'),
    ]);

    return redirect()->away($session->url);
})->middleware('web')->name('pricing.checkout');

// Wave routes (dynamic pages, auth, subscription, etc.)
Wave::routes();

// Yambo: /install override (debe ir tras Wave::routes para ganar a wave.install)
Route::view("/install", "install");

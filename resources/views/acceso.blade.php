@php
    use App\Http\Controllers\AppTv\AppTvAuthController;

    $t = (string) request('t', '');
    $active = false;
    $name = '';
    $trialEnds = null;
    $user = null;

    try {
        if ($t !== '') {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($t)->authenticate();
            if ($user) {
                $active = AppTvAuthController::isSubscriptionActive($user);
                $name = (string) $user->name;
                $trialEnds = $user->trial_ends_at;
            }
        }
    } catch (\Throwable $e) {
        $active = false;
    }

    // A donde va el QR para suscribirse: pagina propia de la TV (elige plan -> Stripe),
    // autenticada con el mismo token, sin pedir login en el telefono.
    $subscribeUrl = url('/precios-tv' . ($t !== '' ? ('?t=' . urlencode($t)) : ''));

    $qr = null;
    if (! $active) {
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $svg = (new \BaconQrCode\Writer($renderer))->writeString($subscribeUrl);
            $qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            $qr = null;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; }
        body {
            background: #0a0a0a; color: #fff;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 3vh 4vw; overflow-y: auto;
        }
        .verify { text-align: center; color: #6b6b70; }
        .spinner {
            display: inline-block; width: 26px; height: 26px;
            border: 3px solid #232327; border-top-color: #e50914;
            border-radius: 50%; animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .wall {
            display: flex; flex-direction: row; gap: 5vw; align-items: center;
            justify-content: center; max-width: 1100px; width: 100%;
        }
        .left { flex: 1; min-width: 0; }
        .logo { color: #e50914; font-size: clamp(26px, 3.6vw, 40px); font-weight: 800; letter-spacing: 3px; margin-bottom: 16px; }
        h1 { font-size: clamp(22px, 3vw, 34px); font-weight: 800; line-height: 1.15; margin-bottom: 14px; }
        .sub { color: #cfcfd3; font-size: clamp(14px, 1.7vw, 18px); line-height: 1.5; margin-bottom: 22px; }
        .feats { list-style: none; }
        .feats li {
            display: flex; align-items: center; gap: 12px; margin-bottom: 10px;
            font-size: clamp(13px, 1.5vw, 16px); color: #ddd;
        }
        .feats li::before { content: "\2713"; color: #e50914; font-weight: 800; }
        .right { display: flex; flex-direction: column; align-items: center; gap: 16px; flex-shrink: 0; }
        .qr-box { background: #fff; border-radius: 16px; padding: 14px; line-height: 0; }
        .qr-box img { width: clamp(180px, 24vw, 240px); height: clamp(180px, 24vw, 240px); display: block; }
        .qr-cap { color: #9b9b9f; font-size: clamp(12px, 1.5vw, 15px); text-align: center; max-width: 260px; }
        .qr-cap strong { color: #fff; }
    </style>
</head>
<body>
@if($active)
    <div class="verify"><span class="spinner"></span></div>
    <script>
        // Acceso confirmado (suscripcion o prueba vigente) -> entrar a la app
        setTimeout(function () { window.location.href = 'yambotvapp://access-ok'; }, 150);
    </script>
@else
    <div class="wall">
        <div class="left">
            <div class="logo">YAMBO TV</div>
            <h1>Tu prueba gratis termino</h1>
            <div class="sub">Suscribete para seguir disfrutando de todo el catalogo: peliculas, series, anime y TV en vivo, sin limites.</div>
            <ul class="feats">
                <li>Miles de peliculas y series</li>
                <li>Estrenos y contenido nuevo cada semana</li>
                <li>Sin anuncios, en todos tus dispositivos</li>
            </ul>
        </div>
        <div class="right">
            @if($qr)
                <div class="qr-box"><img src="{{ $qr }}" alt="QR"></div>
            @else
                <div class="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data={{ urlencode($subscribeUrl) }}" alt="QR"></div>
            @endif
            <div class="qr-cap">Escanea con tu telefono para suscribirte. Entraras directo a tu cuenta.<br>o entra a <strong>tv.yammbo.com/precios-tv</strong></div>
        </div>
    </div>
    <script>
        // Reconsulta cada 5s contra un endpoint JSON (NO contra este HTML: contendria
        // la propia cadena de exito y el paywall se abriria solo).
        (function () {
            var T = @json($t);
            if (!T) { return; }
            setInterval(function () {
                fetch('/api/app-tv/acceso-check?t=' + encodeURIComponent(T), {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (d && d.active === true) { window.location.href = 'yambotvapp://access-ok'; }
                    })
                    .catch(function () {});
            }, 5000);
        })();
    </script>
@endif
</body>
</html>

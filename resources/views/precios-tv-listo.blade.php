<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listo - Yammbo Tv</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0a0a0a; color: #fff; min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center; padding: 28px 20px;
            text-align: center;
        }
        .box { max-width: 420px; }
        .check {
            width: 82px; height: 82px; border-radius: 50%; background: rgba(56,161,105,.15);
            color: #4ade80; font-size: 42px; line-height: 82px; margin: 0 auto 22px;
        }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 10px; }
        p { color: #bfbfbf; font-size: 15px; line-height: 1.5; margin-bottom: 22px; }
        .tv-note {
            background: #141416; border: 1px solid #232327; border-radius: 14px;
            padding: 18px; color: #cfcfd3; font-size: 14px; line-height: 1.55;
        }
        .tv-note strong { color: #fff; }
        .cta {
            display: inline-block; margin-top: 18px; padding: 14px 26px; border-radius: 10px;
            background: #e50914; color: #fff; text-decoration: none; font-weight: 700; font-size: 15px;
        }
        .brand { color: #e50914; font-weight: 800; letter-spacing: 3px; margin-top: 26px; font-size: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="check">&#10003;</div>
        <h1>Suscripción activada</h1>
        <p>Gracias. Tu pago se completó correctamente.</p>
        @if(($src ?? '') === 'web')
            {{-- Pago hecho desde la web: ahi el contenido no reproduce, asi que lo
                 util es que instale las apps, no devolverlo al navegador. --}}
            <div class="tv-note">
                <strong>Ya puedes instalar Yammbo Tv.</strong><br>
                Entra con esta misma cuenta y tendrás tu suscripción activa.
            </div>
            <a class="cta" href="/install">Instalar en móvil o TV</a>
        @else
            {{-- Pago desde el movil o desde el QR de la TV: la app consulta el
                 acceso cada pocos segundos, asi que se desbloquea sola. --}}
            <div class="tv-note">
                <strong>Vuelve a la aplicación.</strong><br>
                Se desbloqueará sola en unos segundos.<br>
                Si no ocurre, ciérrala y vuelve a abrirla.
            </div>
        @endif
        <div class="brand">YAMBO TV</div>
    </div>
</body>
</html>

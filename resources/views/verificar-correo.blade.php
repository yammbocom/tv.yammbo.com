<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar correo - Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0a0a0a; color: #fff; min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 28px 20px; text-align: center;
        }
        .box { max-width: 420px; }
        .icon {
            width: 82px; height: 82px; border-radius: 50%;
            font-size: 40px; line-height: 82px; margin: 0 auto 22px;
        }
        .ok  { background: rgba(56,161,105,.15); color: #4ade80; }
        .bad { background: rgba(229,9,20,.15); color: #f87171; }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 10px; }
        p { color: #bfbfbf; font-size: 15px; line-height: 1.5; }
        .brand { color: #e50914; font-weight: 800; letter-spacing: 3px; margin-top: 28px; font-size: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon {{ $ok ? 'ok' : 'bad' }}">{!! $ok ? '&#10003;' : '&#33;' !!}</div>
        <h1>{{ $ok ? 'Correo confirmado' : 'No pudimos confirmarlo' }}</h1>
        <p>{{ $msg }}</p>
        <div class="brand">YAMBO TV</div>
    </div>
</body>
</html>

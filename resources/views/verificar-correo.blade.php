<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar correo - Yammbo Tv</title>
    <link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg); color: #fff; min-height: 100vh;
            font-family:var(--font);
            display: flex; align-items: center; justify-content: center;
            padding: 28px 20px; text-align: center;
        }
        .box { max-width: 420px; }
        .icon {
            width: 82px; height: 82px; border-radius: 50%;
            font-size: 40px; line-height: 82px; margin: 0 auto 22px;
        }
        .ok  { background: var(--soft); border: 1px solid var(--line-strong); color: var(--fg); }
        .bad { background: var(--soft); border: 1px solid var(--accent); color: var(--accent-text); }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 10px; }
        p { color: var(--muted); font-size: 15px; line-height: 1.5; }
        .brand { color: var(--accent-text); font-weight: 800; letter-spacing: -.02em; margin-top: 28px; font-size: 15px; }
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

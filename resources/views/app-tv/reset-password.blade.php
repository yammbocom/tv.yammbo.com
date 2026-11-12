<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Nueva contraseña · Yammbo TV</title>
    <link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg);color:#fff;
            font-family:var(--font);
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:var(--soft);border:1px solid var(--line);border-radius:var(--radius);padding:32px 28px;max-width:420px;width:100%;}
        .logo{text-align:center;margin-bottom:22px}
        .logo strong{color:var(--accent-text);font-size:26px;letter-spacing:-.02em;font-weight:800}
        h1{font-size:21px;font-weight:700;margin-bottom:8px;text-align:center}
        .subtitle{color:var(--muted);font-size:14px;text-align:center;margin-bottom:22px}
        label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px;margin-top:4px}
        input[type=password],input[type=email]{width:100%;background:var(--soft);border:1px solid var(--line-strong);
            color:#fff;padding:13px 14px;border-radius:8px;font-size:16px;margin-bottom:16px}
        input:focus{outline:none;border-color:var(--accent)}
        input[readonly]{color:var(--muted)}
        button{width:100%;background:var(--accent);color:#fff;border:none;padding:14px;border-radius:8px;
            font-size:16px;font-weight:600;cursor:pointer;margin-top:6px}
        button:hover{background:var(--accent-hover)}
        .error{background:var(--soft);border:1px solid var(--accent);padding:10px 14px;
            border-radius:4px;font-size:14px;margin-bottom:16px;color:var(--accent-text)}
        .success{background:var(--soft);border:1px solid var(--line-strong);padding:16px;
            border-radius:6px;font-size:15px;text-align:center;color:var(--fg)}
        .hint{color:var(--muted);font-size:12px;text-align:center;margin-top:16px}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><strong>YAMMBO TV</strong></div>

        @if (!empty($done))
            <h1>¡Contraseña actualizada!</h1>
            <div class="success">
                Ya puedes iniciar sesión con tu nueva contraseña desde la app o el TV.
            </div>
            <p class="hint">Puedes cerrar esta ventana.</p>
        @else
            <h1>Nueva contraseña</h1>
            <div class="subtitle">Elige una contraseña nueva para tu cuenta.</div>

            @if (!empty($error))
                <div class="error">{{ $error }}</div>
            @endif

            <form method="POST" action="{{ url('/auth/reset-password') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label for="email">Correo</label>
                <input type="email" id="email" name="email" value="{{ $email }}" readonly>

                <label for="password">Nueva contraseña (mínimo 6)</label>
                <input type="password" id="password" name="password" required autofocus autocomplete="new-password">

                <label for="password_confirmation">Repite la contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">

                <button type="submit">Guardar contraseña</button>
            </form>
            <p class="hint">Si no pediste este cambio, ignora este mensaje.</p>
        @endif
    </div>
</body>
</html>

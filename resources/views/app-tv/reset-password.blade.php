<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Nueva contraseña · Yammbo TV</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:#0a0a0a;color:#fff;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#181818;border-radius:16px;padding:32px 28px;max-width:420px;width:100%;
            box-shadow:0 8px 32px rgba(229,9,20,.15)}
        .logo{text-align:center;margin-bottom:22px}
        .logo strong{color:#e50914;font-size:26px;letter-spacing:2px;font-weight:800}
        h1{font-size:21px;font-weight:700;margin-bottom:8px;text-align:center}
        .subtitle{color:#bfbfbf;font-size:14px;text-align:center;margin-bottom:22px}
        label{display:block;font-size:13px;color:#bfbfbf;margin-bottom:6px;margin-top:4px}
        input[type=password],input[type=email]{width:100%;background:#0a0a0a;border:1px solid #333;
            color:#fff;padding:13px 14px;border-radius:8px;font-size:16px;margin-bottom:16px}
        input:focus{outline:none;border-color:#e50914}
        input[readonly]{color:#8a8a8a}
        button{width:100%;background:#e50914;color:#fff;border:none;padding:14px;border-radius:8px;
            font-size:16px;font-weight:600;cursor:pointer;margin-top:6px}
        button:hover{background:#c40811}
        .error{background:rgba(229,9,20,.15);border-left:3px solid #e50914;padding:10px 14px;
            border-radius:4px;font-size:14px;margin-bottom:16px;color:#ffaaaa}
        .success{background:rgba(56,161,105,.15);border-left:3px solid #38a169;padding:16px;
            border-radius:6px;font-size:15px;text-align:center;color:#9ae6b4}
        .hint{color:#7a7a7a;font-size:12px;text-align:center;margin-top:16px}
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

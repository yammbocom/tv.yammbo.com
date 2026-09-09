<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo - Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background:#0a0a0a; color:#fff; min-height:100vh;
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            display:flex; align-items:center; justify-content:center; padding:28px 20px; text-align:center;
        }
        .box { max-width:440px; }
        .icon { width:82px;height:82px;border-radius:50%;background:rgba(229,9,20,.15);color:#e50914;
                font-size:38px;line-height:82px;margin:0 auto 22px; }
        h1 { font-size:24px;font-weight:800;margin-bottom:10px; }
        p { color:#bfbfbf;font-size:15px;line-height:1.55;margin-bottom:22px; }
        strong { color:#fff; }
        form { display:inline; }
        button { background:#e50914;color:#fff;border:0;padding:14px 26px;border-radius:12px;
                 font-size:15px;font-weight:700;cursor:pointer; }
        .brand { color:#e50914;font-weight:800;letter-spacing:3px;margin-top:26px;font-size:15px; }
    </style>
</head>
<body>
<div class="box">
    <div class="icon">&#9993;</div>
    <h1>Verifica tu correo electronico</h1>
    <p>Te enviamos un enlace de confirmacion a <strong>{{ auth()->user()->email ?? 'tu correo' }}</strong>.<br>
       Abrelo para poder ingresar.</p>
    <form method="POST" action="{{ route('app-tv.reenviar-verificacion') }}">
        @csrf
        <input type="hidden" name="email" value="{{ auth()->user()->email ?? '' }}">
        <button type="submit">Reenviar el correo</button>
    </form>
    <div class="brand">YAMBO TV</div>
</div>
</body>
</html>

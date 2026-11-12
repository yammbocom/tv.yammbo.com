@php
    $statusMessages = [
        'missing' => 'Ingresa el codigo de 8 caracteres que aparece en tu TV.',
        'ready' => 'Inicia sesion con tu cuenta para vincular tu TV.',
        'invalid' => 'El codigo no existe o es invalido.',
        'expired' => 'El codigo expiro. Genera uno nuevo desde tu TV.',
        'already_linked' => 'Este codigo ya fue usado para vincular una TV.',
        'success' => 'Vinculacion exitosa. Vuelve a tu TV en unos segundos.',
        'registered' => 'Cuenta creada. Verifica tu correo y tu TV entrara sola.',
    ];
    $message = $statusMessages[$status] ?? '';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vincular Yammbo Tv</title>
    <link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: #fff;
            font-family:var(--font);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius:var(--radius);
            padding: 32px 28px;
            max-width: 420px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo img {
            height: 56px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }
        .subtitle {
            color: var(--muted);
            font-size: 14px;
            text-align: center;
            margin-bottom: 24px;
        }
        .code-box {
            background: var(--soft);
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 4px;
            margin-bottom: 24px;
            color: var(--accent-text);
        }
        label {
            display: block;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            background: var(--soft);
            border: 1px solid var(--line-strong);
            color: #fff;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 16px;
        }
        input:focus {
            outline: none;
            border-color: var(--accent);
        }
        button {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }
        button:hover { background: var(--accent-hover); }
        button:disabled { background: var(--line); color: var(--muted); cursor: not-allowed; }
        .error {
            background: var(--soft);
            border: 1px solid var(--accent);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 16px;
            color: var(--accent-text);
        }
        .success {
            background: var(--soft);
            border: 1px solid var(--line-strong);
            padding: 14px;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            color: var(--fg);
        }
        .info {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
            margin-bottom: 16px;
        }
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .tabs .tab {
            flex: 1;
            width: auto;
            margin: 0;
            background: var(--soft);
            border: 1px solid var(--line-strong);
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            padding: 11px;
        }
        .tabs .tab.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .tabs .tab:hover { background: var(--soft-2); }
        .tabs .tab.active:hover { background: var(--accent-hover); }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <strong style="color:var(--accent-text); font-size:28px; letter-spacing:-.02em;">YAMBO TV</strong>
        </div>

        @if ($status === 'success')
            <h1>Listo!</h1>
            <div class="success">{{ $message }}</div>
        @elseif ($status === 'already_linked')
            <h1>Ya vinculado</h1>
            <div class="info">{{ $message }}</div>
        @elseif (in_array($status, ['invalid', 'expired']))
            <h1>Codigo no valido</h1>
            <div class="error">{{ $message }}</div>
            <p class="info">Vuelve a tu TV y genera un codigo nuevo desde la pantalla de inicio de sesion.</p>
        @elseif ($status === 'missing')
            <h1>Vincular TV</h1>
            <div class="info">{{ $message }}</div>
            <form method="GET" action="{{ url('/tv-link') }}">
                <label for="code">Codigo de 8 caracteres</label>
                <input type="text" id="code" name="code" maxlength="8" required pattern="[A-Za-z0-9]{8}" autofocus
                    style="text-transform:uppercase; letter-spacing:4px; text-align:center; font-size:20px;">
                <button type="submit">Continuar</button>
            </form>
        @elseif ($status === 'registered')
            <h1>Cuenta creada!</h1>
            <div class="code-box">{{ $code }}</div>
            <div class="info" style="margin-bottom:8px;">
                Te enviamos un correo para <b>verificar tu cuenta</b>.
                Abrelo desde tu telefono y, en cuanto lo confirmes, tu TV entrara sola.
            </div>
            <div class="subtitle">Tienes 7 dias de prueba gratis al confirmar.</div>
        @else
            @php($mode = $mode ?? 'login')
            <h1 id="ttl">{{ $mode === 'register' ? 'Crear cuenta' : 'Iniciar sesion' }}</h1>
            <div class="subtitle">Vincula tu TV: inicia sesion o crea una cuenta nueva.</div>
            <div class="code-box">{{ $code }}</div>
            @if ($error)
                <div class="error">{{ $error }}</div>
            @endif

            {{-- Pestanas --}}
            <div class="tabs">
                <button type="button" class="tab {{ $mode !== 'register' ? 'active' : '' }}" onclick="ymbTab('login')" id="tab-login">Ya tengo cuenta</button>
                <button type="button" class="tab {{ $mode === 'register' ? 'active' : '' }}" onclick="ymbTab('register')" id="tab-register">Soy nuevo</button>
            </div>

            {{-- Iniciar sesion --}}
            <form method="POST" action="{{ url('/tv-link/confirm') }}" id="form-login" style="{{ $mode === 'register' ? 'display:none;' : '' }}">
                @csrf
                <input type="hidden" name="code" value="{{ $code }}">
                <label for="email">Correo</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Iniciar sesion y vincular</button>
            </form>

            {{-- Crear cuenta --}}
            <form method="POST" action="{{ url('/tv-link/register') }}" id="form-register" style="{{ $mode === 'register' ? '' : 'display:none;' }}">
                @csrf
                <input type="hidden" name="code" value="{{ $code }}">
                <label for="rname">Nombre</label>
                <input type="text" id="rname" name="name" value="{{ old('name') }}" autocomplete="name">
                <label for="remail">Correo</label>
                <input type="email" id="remail" name="email" value="{{ old('email') }}" autocomplete="email">
                <label for="rpassword">Contrasena (minimo 6)</label>
                <input type="password" id="rpassword" name="password" autocomplete="new-password">
                <button type="submit">Crear cuenta y vincular</button>
            </form>

            <script>
                function ymbTab(m){
                    var login = m === 'login';
                    document.getElementById('form-login').style.display = login ? '' : 'none';
                    document.getElementById('form-register').style.display = login ? 'none' : '';
                    document.getElementById('tab-login').classList.toggle('active', login);
                    document.getElementById('tab-register').classList.toggle('active', !login);
                    document.getElementById('ttl').textContent = login ? 'Iniciar sesion' : 'Crear cuenta';
                }
            </script>
        @endif
    </div>
</body>
</html>

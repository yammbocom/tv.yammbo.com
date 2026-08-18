@php
    $statusMessages = [
        'missing' => 'Ingresa el codigo de 8 caracteres que aparece en tu TV.',
        'ready' => 'Inicia sesion con tu cuenta para vincular tu TV.',
        'invalid' => 'El codigo no existe o es invalido.',
        'expired' => 'El codigo expiro. Genera uno nuevo desde tu TV.',
        'already_linked' => 'Este codigo ya fue usado para vincular una TV.',
        'success' => 'Vinculacion exitosa. Vuelve a tu TV en unos segundos.',
    ];
    $message = $statusMessages[$status] ?? '';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vincular Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #181818;
            border-radius: 16px;
            padding: 32px 28px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(229, 9, 20, 0.15);
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
            color: #bfbfbf;
            font-size: 14px;
            text-align: center;
            margin-bottom: 24px;
        }
        .code-box {
            background: #0a0a0a;
            border: 2px solid #e50914;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 4px;
            margin-bottom: 24px;
            color: #e50914;
        }
        label {
            display: block;
            font-size: 13px;
            color: #bfbfbf;
            margin-bottom: 6px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #fff;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 16px;
        }
        input:focus {
            outline: none;
            border-color: #e50914;
        }
        button {
            width: 100%;
            background: #e50914;
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }
        button:hover { background: #c40811; }
        button:disabled { background: #555; cursor: not-allowed; }
        .error {
            background: rgba(229, 9, 20, 0.15);
            border-left: 3px solid #e50914;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 16px;
            color: #ffaaaa;
        }
        .success {
            background: rgba(56, 161, 105, 0.15);
            border-left: 3px solid #38a169;
            padding: 14px;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            color: #9ae6b4;
        }
        .info {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            color: #bfbfbf;
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <strong style="color:#e50914; font-size:28px; letter-spacing:2px;">YAMBO TV</strong>
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
        @else
            <h1>Iniciar sesion</h1>
            <div class="subtitle">{{ $message }}</div>
            <div class="code-box">{{ $code }}</div>
            @if ($error)
                <div class="error">{{ $error }}</div>
            @endif
            <form method="POST" action="{{ url('/tv-link/confirm') }}">
                @csrf
                <input type="hidden" name="code" value="{{ $code }}">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Vincular TV</button>
            </form>
        @endif
    </div>
</body>
</html>

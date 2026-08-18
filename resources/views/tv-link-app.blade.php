<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
        }
        .container {
            display: flex;
            flex-direction: row;
            gap: 48px;
            align-items: center;
            max-width: 1200px;
            width: 100%;
        }
        .left { flex: 1; min-width: 0; }
        .logo {
            color: #e50914;
            font-size: 44px;
            font-weight: 800;
            letter-spacing: 3px;
            margin-bottom: 18px;
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 14px;
            line-height: 1.2;
        }
        .subtitle {
            color: #bfbfbf;
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .steps { list-style: none; counter-reset: step; }
        .steps li {
            counter-increment: step;
            position: relative;
            padding-left: 56px;
            margin-bottom: 18px;
            font-size: 17px;
            line-height: 1.5;
            color: #ddd;
        }
        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 0; top: 0;
            width: 40px; height: 40px;
            background: #e50914;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }
        .right { display: flex; flex-direction: column; align-items: center; gap: 24px; }
        .qr-card {
            background: #fff;
            padding: 18px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(229, 9, 20, 0.25);
        }
        .qr-card img { display: block; width: 280px; height: 280px; }
        .code-display { text-align: center; }
        .code-label {
            color: #888;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .code-value {
            background: #181818;
            border: 2px solid #e50914;
            border-radius: 12px;
            padding: 18px 28px;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #e50914;
            font-family: "Courier New", monospace;
        }
        .url-line { color: #888; font-size: 14px; margin-top: 8px; }
        .url-line strong { color: #fff; }
        .status {
            margin-top: 12px;
            color: #888;
            font-size: 14px;
            text-align: center;
            min-height: 20px;
        }
        .status.linked { color: #38a169; font-weight: 600; }
        .status.error { color: #e50914; }
        .spinner {
            display: inline-block;
            width: 12px; height: 12px;
            border: 2px solid #888;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 6px;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-state { text-align: center; color: #bfbfbf; padding: 40px 20px; }
        @media (max-width: 720px) {
            .container { flex-direction: column; gap: 24px; }
            .qr-card img { width: 220px; height: 220px; }
            .code-value { font-size: 28px; padding: 14px 20px; }
            h1 { font-size: 24px; }
            .subtitle { font-size: 15px; }
            .steps li { font-size: 15px; padding-left: 48px; }
            .steps li::before { width: 34px; height: 34px; font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="logo">YAMBO TV</div>
            <h1>Inicia sesion en tu TV</h1>
            <div class="subtitle">Escanea el codigo QR con tu telefono o ingresa el codigo manualmente desde un navegador.</div>
            <ol class="steps">
                <li>Escanea el QR con la camara de tu telefono o entra a <strong>tv.yammbo.com/tv-link</strong></li>
                <li>Ingresa el codigo de 8 caracteres que aparece a la derecha</li>
                <li>Inicia sesion con tu correo y contrasena de Yambo TV</li>
                <li>Tu TV se vinculara automaticamente</li>
            </ol>
        </div>

        <div class="right">
            <div id="content">
                <div class="loading-state">
                    <div class="spinner"></div>
                    Generando codigo...
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var pollInterval = null;
            var currentCode = null;

            function api(path, opts) {
                return fetch(path, Object.assign({
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                }, opts || {}));
            }

            function buildQrUrl(confirmUrl) {
                var data = encodeURIComponent(confirmUrl);
                return 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=0&data=' + data;
            }

            function renderReady(data) {
                currentCode = data.code;
                var qrSrc = buildQrUrl(data.confirm_url);
                document.getElementById('content').innerHTML =
                    '<div class="qr-card"><img src="' + qrSrc + '" alt="QR" /></div>' +
                    '<div class="code-display">' +
                        '<div class="code-label">Codigo</div>' +
                        '<div class="code-value">' + data.code + '</div>' +
                        '<div class="url-line">o entra a <strong>tv.yammbo.com/tv-link</strong></div>' +
                    '</div>' +
                    '<div class="status" id="poll-status"><span class="spinner"></span>Esperando vinculacion...</div>';
                startPolling();
            }

            function renderError(message) {
                document.getElementById('content').innerHTML =
                    '<div class="loading-state" style="color:#e50914;">' +
                        '<strong>Error:</strong> ' + message +
                        '<br><br><button onclick="location.reload()" style="background:#e50914;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:14px;cursor:pointer;">Reintentar</button>' +
                    '</div>';
            }

            function generate() {
                api('/api/app-tv/tv-link/generate', { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.code) renderReady(data);
                        else renderError('Respuesta invalida del servidor');
                    })
                    .catch(function () { renderError('No se pudo conectar al servidor'); });
            }

            function poll() {
                if (!currentCode) return;
                api('/api/app-tv/tv-link/poll', {
                    method: 'POST',
                    body: JSON.stringify({ code: currentCode }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var status = document.getElementById('poll-status');
                        if (!data || !data.status) return;
                        if (data.status === 'linked' && data.user) {
                            if (status) {
                                status.className = 'status linked';
                                status.innerHTML = 'Vinculado! Abriendo Yambo TV...';
                            }
                            clearInterval(pollInterval);
                            pollInterval = null;
                            var subActive = data.subscription_active ? '1' : '0';
                            var url = 'yambotvapp://authorized'
                                + '?name=' + encodeURIComponent(data.user.name || '')
                                + '&email=' + encodeURIComponent(data.user.email || '')
                                + '&user_id=' + encodeURIComponent(String(data.user.id || ''))
                                + '&subscription_active=' + subActive;
                            setTimeout(function () { location.href = url; }, 800);
                        } else if (data.status === 'expired') {
                            if (status) {
                                status.className = 'status error';
                                status.textContent = 'Codigo expirado';
                            }
                            clearInterval(pollInterval);
                            pollInterval = null;
                            setTimeout(function () { location.reload(); }, 1500);
                        }
                    })
                    .catch(function () { /* keep polling */ });
            }

            function startPolling() {
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(poll, 3000);
            }

            generate();
        })();
    </script>
</body>
</html>

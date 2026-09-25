<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Yammbo Tv</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3vh 4vw;
            overflow-y: auto;
        }
        .container {
            display: flex;
            flex-direction: row;
            gap: 5vw;
            align-items: center;
            justify-content: center;
            max-width: 1200px;
            width: 100%;
        }
        .left { flex: 1; min-width: 0; }
        .logo {
            color: #e50914;
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 800;
            letter-spacing: 3px;
            margin-bottom: 14px;
        }
        h1 {
            font-size: clamp(20px, 2.6vw, 30px);
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .subtitle {
            color: #bfbfbf;
            font-size: clamp(13px, 1.5vw, 17px);
            line-height: 1.45;
            margin-bottom: 20px;
        }
        .steps { list-style: none; counter-reset: step; }
        .steps li {
            counter-increment: step;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
            font-size: clamp(13px, 1.4vw, 16px);
            line-height: 1.35;
            color: #ddd;
        }
        .steps li::before {
            content: counter(step);
            flex-shrink: 0;
            width: 34px; height: 34px;
            background: #e50914;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        .right { display: flex; flex-direction: column; align-items: center; gap: 18px; flex-shrink: 0; }
        .qr-card {
            background: #fff;
            padding: 14px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(229, 9, 20, 0.25);
            line-height: 0;
        }
        .qr-card img { display: block; width: clamp(180px, 24vw, 260px); height: clamp(180px, 24vw, 260px); }
        .code-display { text-align: center; }
        .code-label {
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .code-value {
            background: #181818;
            border: 2px solid #e50914;
            border-radius: 12px;
            padding: 14px 22px;
            font-size: clamp(26px, 3vw, 34px);
            font-weight: 800;
            letter-spacing: 8px;
            color: #e50914;
            font-family: "Courier New", monospace;
        }
        .url-line { color: #888; font-size: 13px; margin-top: 8px; }
        .url-line strong { color: #fff; }
        .status {
            margin-top: 10px;
            color: #888;
            font-size: 13px;
            text-align: center;
            min-height: 18px;
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
        .loading-state { text-align: center; color: #bfbfbf; padding: 30px 20px; }
        .btn-retry { background:#e50914;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:14px;cursor:pointer;margin-top:16px; }
        @media (max-width: 760px) {
            .container { flex-direction: column; gap: 20px; }
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
                <li>Inicia sesion con tu correo y contrasena de Yammbo Tv</li>
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

            function qrFallback(confirmUrl) {
                return 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=0&data=' + encodeURIComponent(confirmUrl);
            }

            function renderReady(data) {
                currentCode = data.code;
                var primary = data.qr || qrFallback(data.confirm_url);
                var fb = qrFallback(data.confirm_url);
                document.getElementById('content').innerHTML =
                    '<div class="qr-card"><img id="qr-img" src="' + primary + '" alt="QR" /></div>' +
                    '<div class="code-display">' +
                        '<div class="code-label">Codigo</div>' +
                        '<div class="code-value">' + data.code + '</div>' +
                        '<div class="url-line">o entra a <strong>tv.yammbo.com/tv-link</strong></div>' +
                    '</div>' +
                    '<div class="status" id="poll-status"><span class="spinner"></span>Esperando vinculacion...</div>';
                var img = document.getElementById('qr-img');
                if (img) {
                    img.onerror = function () { if (img.src !== fb) { img.onerror = null; img.src = fb; } };
                }
                startPolling();
            }

            function renderError(message) {
                document.getElementById('content').innerHTML =
                    '<div class="loading-state" style="color:#e50914;">' +
                        '<strong>Error:</strong> ' + message +
                        '<br><button class="btn-retry" onclick="location.reload()">Reintentar</button>' +
                    '</div>';
            }

            function generate(attempt) {
                attempt = attempt || 1;
                var MAX = 5;
                api('/api/app-tv/tv-link/generate', { method: 'POST' })
                    .then(function (r) { return r.json().catch(function () { return null; }); })
                    .then(function (data) {
                        if (data && data.code) { renderReady(data); return; }
                        if (attempt < MAX) { setTimeout(function () { generate(attempt + 1); }, 1300); }
                        else { renderError('No se pudo generar el codigo. Revisa tu conexion.'); }
                    })
                    .catch(function () {
                        if (attempt < MAX) { setTimeout(function () { generate(attempt + 1); }, 1300); }
                        else { renderError('No se pudo conectar al servidor.'); }
                    });
            }

            function poll() {
                if (!currentCode) return;
                api('/api/app-tv/tv-link/poll', { method: 'POST', body: JSON.stringify({ code: currentCode }) })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var status = document.getElementById('poll-status');
                        if (!data || !data.status) return;
                        if (data.status === 'linked' && data.user) {
                            if (status) { status.className = 'status linked'; status.innerHTML = 'Vinculado! Abriendo Yammbo Tv...'; }
                            clearInterval(pollInterval); pollInterval = null;
                            var subActive = data.subscription_active ? '1' : '0';
                            var url = 'yambotvapp://authorized'
                                + '?name=' + encodeURIComponent(data.user.name || '')
                                + '&email=' + encodeURIComponent(data.user.email || '')
                                + '&user_id=' + encodeURIComponent(String(data.user.id || ''))
                                + '&subscription_active=' + subActive
                                + '&manage_token=' + encodeURIComponent(data.manage_token || '');
                            setTimeout(function () { location.href = url; }, 800);
                        } else if (data.status === 'expired') {
                            if (status) { status.className = 'status error'; status.textContent = 'Codigo expirado'; }
                            clearInterval(pollInterval); pollInterval = null;
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

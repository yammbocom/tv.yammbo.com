@php
    $name  = trim((string) request('name', 'Usuario'));
    $email = strtolower(trim((string) request('email', '')));
    $sub   = (string) request('sub', '0');
    $mt    = (string) request('mt', '');
    if ($name === '') { $name = 'Usuario'; }

    $initial = mb_strtoupper(mb_substr($name, 0, 1));
    $gravatar = $email !== ''
        ? 'https://www.gravatar.com/avatar/' . md5($email) . '?s=240&d=404'
        : '';

    $manageUrl = url('/mi-suscripcion' . ($mt !== '' ? ('?t=' . urlencode($mt)) : ''));

    $qr = null;
    try {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $svg = (new \BaconQrCode\Writer($renderer))->writeString($manageUrl);
        $qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
    } catch (\Throwable $e) {
        $qr = null;
    }

    $subActive = ($sub === '1' || $sub === 'true');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Yambo TV</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            background: #0a0a0a; color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 4vh 5vw; overflow-y: auto;
        }
        .card {
            width: 100%; max-width: 640px;
            background: #141416; border-radius: 20px;
            padding: clamp(24px, 4vw, 40px);
        }
        .head { display: flex; align-items: center; gap: 22px; margin-bottom: 28px; }
        .avatar {
            width: clamp(84px, 12vw, 108px); height: clamp(84px, 12vw, 108px);
            border-radius: 50%; flex-shrink: 0; overflow: hidden;
            background: #e50914; display: flex; align-items: center; justify-content: center;
            font-size: clamp(36px, 5vw, 48px); font-weight: 800; color: #fff;
            border: 3px solid rgba(229,9,20,0.5);
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .who h1 { font-size: clamp(22px, 3vw, 30px); font-weight: 700; margin-bottom: 4px; }
        .who .email { color: #9b9b9f; font-size: clamp(13px, 1.6vw, 16px); word-break: break-all; }
        .who .change { margin-top: 8px; }
        .change button {
            background: transparent; border: 1px solid #3a3a3f; color: #cfcfd3;
            padding: 6px 14px; border-radius: 999px; font-size: 12px; cursor: pointer;
        }
        .rows { border-top: 1px solid #232327; }
        .row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 2px; border-bottom: 1px solid #232327; gap: 16px;
        }
        .row .label { color: #9b9b9f; font-size: clamp(13px, 1.6vw, 16px); }
        .row .value { font-weight: 600; font-size: clamp(14px, 1.7vw, 17px); text-align: right; }
        .badge {
            display: inline-block; padding: 5px 14px; border-radius: 999px;
            font-size: 13px; font-weight: 700;
        }
        .badge.on  { background: rgba(56,161,105,0.15); color: #4ade80; }
        .badge.off { background: rgba(229,9,20,0.15); color: #f87171; }
        .manage {
            width: 100%; margin-top: 28px; padding: 16px;
            background: #e50914; color: #fff; border: none; border-radius: 14px;
            font-size: clamp(15px, 1.8vw, 18px); font-weight: 700; cursor: pointer;
        }
        .hint { text-align: center; color: #6b6b70; font-size: 12px; margin-top: 14px; }

        .modal {
            position: fixed; inset: 0; background: rgba(0,0,0,0.88);
            display: none; align-items: center; justify-content: center;
            padding: 3vh 4vw; z-index: 10; overflow-y: auto;
        }
        .modal.show { display: flex; }
        .modal-card {
            background: #141416; border-radius: 20px; padding: clamp(16px, 2.5vh, 26px); text-align: center;
            max-width: 420px; width: 100%; max-height: 94vh; overflow-y: auto; margin: auto;
        }
        .modal-card h2 { font-size: clamp(16px, 2.4vh, 20px); margin-bottom: 6px; }
        .modal-card p { color: #9b9b9f; font-size: clamp(12px, 1.8vh, 14px); margin-bottom: clamp(10px, 2vh, 18px); line-height: 1.35; }
        .qr-box { background: #fff; border-radius: 14px; padding: 12px; display: inline-block; line-height: 0; }
        .qr-box img { width: min(34vh, 240px); height: min(34vh, 240px); display: block; }
        .modal-url { color: #9b9b9f; font-size: 13px; margin-top: 16px; word-break: break-all; }
        .modal-url strong { color: #fff; }
        .modal-close {
            margin-top: 20px; background: transparent; border: 1px solid #3a3a3f;
            color: #cfcfd3; padding: 10px 24px; border-radius: 10px; font-size: 14px; cursor: pointer;
        }
        .avatars { display: grid; grid-template-columns: repeat(4, 1fr); gap: clamp(8px, 1.4vh, 14px); margin: clamp(10px, 2vh, 18px) 0; }
        .avatars .opt {
            cursor: pointer; text-align: center;
        }
        .avatars .opt .pic {
            aspect-ratio: 1; border-radius: 50%; overflow: hidden; border: 3px solid transparent;
            background: #232327; display: flex; align-items: center; justify-content: center;
        }
        .avatars .opt.sel .pic { border-color: #e50914; }
        .avatars .opt .pic img { width: 100%; height: 100%; object-fit: cover; }
        .avatars .opt .pic svg { width: 100%; height: 100%; }
        .avatars .opt .cap {
            font-size: clamp(9px, 1.3vh, 11px); color: #9b9b9f; margin-top: 5px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .avatars-loading { color: #9b9b9f; font-size: 13px; padding: 18px 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <div class="avatar" id="avatar"><span id="avatar-initial">{{ $initial }}</span></div>
            <div class="who">
                <h1>{{ $name }}</h1>
                @if($email !== '')<div class="email">{{ $email }}</div>@endif
                <div class="change"><button onclick="openAvatars()">Cambiar avatar</button></div>
            </div>
        </div>

        <div class="rows">
            <div class="row">
                <span class="label">Suscripcion</span>
                <span class="value">
                    <span class="badge {{ $subActive ? 'on' : 'off' }}">{{ $subActive ? 'ACTIVA' : 'INACTIVA' }}</span>
                </span>
            </div>
            <div class="row">
                <span class="label">Cuenta</span>
                <span class="value">Yambo TV</span>
            </div>
        </div>

        <button class="manage" onclick="openManage()">Administrar suscripcion</button>
        <div class="hint">Escanea el codigo QR con tu telefono para gestionar tu plan y pagos.</div>
    </div>

    <!-- Modal: gestionar suscripcion (QR magic-link) -->
    <div class="modal" id="manage-modal">
        <div class="modal-card">
            <h2>Administrar suscripcion</h2>
            <p>Escanea este codigo con tu telefono. Entraras directo a tu cuenta, sin volver a iniciar sesion.</p>
            @if($qr)
                <div class="qr-box"><img src="{{ $qr }}" alt="QR"></div>
            @else
                <div class="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data={{ urlencode($manageUrl) }}" alt="QR"></div>
            @endif
            <div class="modal-url">o entra a <strong>tv.yammbo.com/mi-suscripcion</strong></div>
            <button class="modal-close" onclick="closeManage()">Cerrar</button>
        </div>
    </div>

    <!-- Modal: elegir avatar -->
    <div class="modal" id="avatar-modal">
        <div class="modal-card">
            <h2>Elige tu avatar</h2>
            <div class="avatars" id="avatar-grid"></div>
            <button class="modal-close" onclick="closeAvatars()">Cerrar</button>
        </div>
    </div>

    <script>
        (function () {
            var gravatar = @json($gravatar);
            var initial = @json($initial);
            var avatarEl = document.getElementById('avatar');

            var CHARS = [];   // personajes del momento (TMDB)

            function readSaved() {
                try { return JSON.parse(localStorage.getItem('yambo_avatar_img') || 'null'); } catch (e) { return null; }
            }

            function applyAvatar() {
                var saved = readSaved();
                if (saved && saved.img) {
                    avatarEl.innerHTML = '<img src="' + saved.img + '" alt="">';
                    return;
                }
                // Gravatar si existe, si no la inicial (ya visible)
                if (gravatar) {
                    var img = new Image();
                    img.onload = function () { avatarEl.innerHTML = '<img src="' + gravatar + '" alt="">'; };
                    img.onerror = function () { /* deja la inicial */ };
                    img.src = gravatar;
                }
            }

            function silhouette(bg) {
                return '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">' +
                       '<rect width="100" height="100" fill="' + bg + '"/>' +
                       '<circle cx="50" cy="40" r="15" fill="#fff"/>' +
                       '<path d="M26 86c0-14 11-22 24-22s24 8 24 22z" fill="#fff"/></svg>';
            }

            function renderGrid() {
                var grid = document.getElementById('avatar-grid');
                var saved = readSaved();
                var items = CHARS.slice();
                // opcion final: silueta neutra (sin personaje)
                items.push({ img: '', name: 'Sin foto', svg: silhouette('#e50914') });

                grid.innerHTML = items.map(function (it, i) {
                    var inner = it.img
                        ? '<img src="' + it.img + '" alt="" loading="lazy">'
                        : (it.svg || silhouette('#374151'));
                    var isSel = saved && ((saved.img && saved.img === it.img) || (!saved.img && !it.img));
                    return '<div class="opt ' + (isSel ? 'sel' : '') + '" data-i="' + i + '">' +
                               '<div class="pic">' + inner + '</div>' +
                               '<div class="cap">' + (it.name || '') + '</div>' +
                           '</div>';
                }).join('');

                Array.prototype.forEach.call(grid.querySelectorAll('.opt'), function (el) {
                    el.onclick = function () {
                        var it = items[parseInt(el.getAttribute('data-i'), 10)];
                        try {
                            if (it && it.img) { localStorage.setItem('yambo_avatar_img', JSON.stringify({ img: it.img, name: it.name })); }
                            else { localStorage.removeItem('yambo_avatar_img'); avatarEl.innerHTML = '<span>' + initial + '</span>'; }
                        } catch (e) {}
                        applyAvatar(); closeAvatars();
                    };
                });
            }

            function loadChars(cb) {
                if (CHARS.length) { cb(); return; }
                fetch('/api/app-tv/avatars', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { CHARS = (d && d.avatars) || []; cb(); })
                    .catch(function () { CHARS = []; cb(); });
            }

            window.openManage = function () { document.getElementById('manage-modal').classList.add('show'); };
            window.closeManage = function () { document.getElementById('manage-modal').classList.remove('show'); };
            window.closeAvatars = function () { document.getElementById('avatar-modal').classList.remove('show'); };
            window.openAvatars = function () {
                document.getElementById('avatar-grid').innerHTML = '<div class="avatars-loading">Cargando personajes...</div>';
                document.getElementById('avatar-modal').classList.add('show');
                loadChars(renderGrid);
            };

            applyAvatar();
        })();
    </script>
</body>
</html>

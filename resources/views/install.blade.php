@php
    $isEs = str_starts_with(app()->getLocale(), 'es');
    $svc  = '/download/YammboTV-Service-Setup-v2.exe';
    $apk  = '/download/YamboTV.apk';
    $vpn  = '/download/VPN-Yammbo.apk';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<title>{{ $isEs ? 'Instalar y reproducir' : 'Install & play' }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:700px;margin:0 auto;padding:32px 20px 80px}
  .logo-wrap{display:flex;justify-content:center;margin-bottom:20px}
  .logo-wrap img{height:42px;width:auto}
  h1{font-size:26px;font-weight:800;text-align:center;margin:0 0 8px;letter-spacing:-.02em}
  .subtitle{text-align:center;color:#bdbdbd;margin:0 0 28px;font-size:15px;line-height:1.55}
  .card{background:#111;border:1px solid #2A2A2A;border-radius:14px;padding:22px 24px;margin-bottom:16px}
  .card h2{margin:0 0 10px;font-size:18px;font-weight:700;display:flex;align-items:center;gap:9px}
  .num{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:#E50914;color:#fff;font-size:14px;font-weight:800;flex:0 0 auto}
  .card p{margin:0 0 12px;color:#ddd;font-size:14px;line-height:1.6}
  .cta-card{background:linear-gradient(135deg,#1a0608 0%,#2a0a0e 100%);border:1px solid #E50914;box-shadow:0 4px 24px rgba(229,9,20,.15)}
  .btn-row{text-align:center;margin:6px 0 2px}
  .cta-btn{display:inline-block;background:#E50914;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:13px 28px;border-radius:10px;letter-spacing:.02em;transition:transform .15s ease,background .15s ease}
  .cta-btn:hover,.cta-btn:active{background:#c8070f;transform:translateY(-1px)}
  .cta-btn svg{vertical-align:-3px;margin-right:8px}
  .cta-btn.ghost{background:transparent;border:1px solid #444;color:#ddd;font-weight:600;font-size:13px;padding:10px 20px}
  .cta-btn.ghost:hover{background:#1a1a1a;border-color:#666}
  .platform{text-align:center;color:#9a9a9a;font-size:12px;margin:10px 0 0}

  /* Switch (toggle) que despliega la guía */
  .toggle-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding-top:16px;border-top:1px solid #2a2a2a}
  .toggle-row .t-label{font-size:13.5px;font-weight:600;color:#eee}
  .acc-input{position:absolute;opacity:0;width:0;height:0}
  .switch{position:relative;display:inline-block;width:46px;height:26px;flex:0 0 auto;cursor:pointer}
  .switch .track{position:absolute;inset:0;background:#3a3a3a;border-radius:999px;transition:.25s}
  .switch .knob{position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.25s}
  .acc-input:checked + .toggle-row .switch .track{background:#E50914}
  .acc-input:checked + .toggle-row .switch .knob{transform:translateX(20px)}
  .acc-content{max-height:0;overflow:hidden;transition:max-height .35s ease}
  .acc-input:checked ~ .acc-content{max-height:1600px}
  .guide{padding-top:14px}
  .guide ol{margin:0;padding-left:20px;color:#cfcfcf;font-size:13.5px;line-height:1.75}
  .guide ol li{margin-bottom:7px}
  .guide ol li b{color:#fff}
  .dialog{background:#0e1726;border:1px solid #24344d;border-radius:10px;padding:13px 15px;margin:14px 0;font-size:13px;line-height:1.6;color:#cdd9ee}
  .dialog .dlg-title{display:flex;align-items:center;gap:8px;font-weight:700;color:#9ec1ff;margin-bottom:6px}
  .dialog .steps{margin:8px 0 0;padding-left:18px}
  .dialog code{background:#1c2741;padding:1px 6px;border-radius:5px;color:#fff;font-size:12.5px}
  .reassure{background:#0d1f12;border:1px solid #1f4d2c;border-radius:10px;padding:11px 14px;margin:12px 0 0;font-size:12.5px;line-height:1.55;color:#a7e0b8}
  .vpn-sub{margin-top:18px;padding-top:16px;border-top:1px dashed #333}
  .vpn-sub h3{margin:0 0 6px;font-size:15px;font-weight:700;color:#fff}
  .vpn-sub p{font-size:13px;color:#cfcfcf;margin:0 0 12px;line-height:1.55}
  .back{display:block;text-align:center;color:#888;text-decoration:none;font-size:13px;margin-top:28px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo-wrap"><img src="/images/yambo-logo.png" alt="Yammbo Tv"></div>
  <h1>{{ $isEs ? 'Pon todo a funcionar' : 'Get everything working' }}</h1>
  <p class="subtitle">{{ $isEs ? 'Descarga lo que necesitas para disfrutar Yammbo Tv al máximo. Toca el interruptor de cada paso para ver la guía detallada.' : 'Download what you need to enjoy Yammbo Tv to the fullest. Flip each switch to see the detailed guide.' }}</p>

  {{-- 1 · Yammbo TV Service (PC / Windows) --}}
  <div class="card cta-card">
    <h2><span class="num">1</span> Yammbo TV Service · PC (Windows)</h2>
    <p>{{ $isEs
        ? 'El reproductor del navegador necesita este pequeño servicio gratuito para reproducir películas y series. Se instala una sola vez y queda corriendo en segundo plano.'
        : 'The browser player needs this small free service to play movies and series. Install it once and leave it running in the background.' }}</p>
    <div class="btn-row">
      <a href="{{ $svc }}" class="cta-btn" download>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>{{ $isEs ? 'Descargar para Windows' : 'Download for Windows' }}
      </a>
    </div>
    <input type="checkbox" id="g1" class="acc-input">
    <label for="g1" class="toggle-row">
      <span class="t-label">{{ $isEs ? 'Ver guía paso a paso + aviso de Windows' : 'Show step-by-step guide + Windows warning' }}</span>
      <span class="switch"><span class="track"></span><span class="knob"></span></span>
    </label>
    <div class="acc-content">
      <div class="guide">
        <ol>
          <li>{{ $isEs ? 'Descarga y abre el instalador' : 'Download and open the installer' }} (<b>YammboTV-Service-Setup.exe</b>).</li>
          <li>{!! $isEs ? 'Windows mostrará un aviso azul — <b>es normal</b>, mira el recuadro de abajo para continuar.' : 'Windows will show a blue warning — <b>this is normal</b>, see the box below to continue.' !!}</li>
          <li>{{ $isEs ? 'Sigue el instalador (Siguiente / Instalar). Termina en segundos.' : 'Follow the installer (Next / Install). It finishes in seconds.' }}</li>
          <li>{!! $isEs ? 'El servicio queda en la <b>bandeja del sistema</b> (junto al reloj, abajo a la derecha).' : 'The service stays in the <b>system tray</b> (next to the clock, bottom-right).' !!}</li>
          <li>{!! $isEs ? 'Vuelve a <b>tv.yammbo.com</b>, recarga, y ya puedes reproducir.' : 'Go back to <b>tv.yammbo.com</b>, reload, and you can play.' !!}</li>
        </ol>
        <div class="dialog">
          <div class="dlg-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            {{ $isEs ? '“Windows protegió tu PC” / Editor desconocido' : '“Windows protected your PC” / Unknown publisher' }}
          </div>
          {{ $isEs
            ? 'Aparece una pantalla azul de Windows Defender SmartScreen. NO es un virus: sale porque la app no tiene una licencia de firma digital (un certificado de pago anual). Para continuar:'
            : 'A blue Windows Defender SmartScreen screen appears. It is NOT a virus: it shows because the app does not have a paid digital-signature license. To continue:' }}
          <ol class="steps">
            <li>{!! $isEs ? 'Haz clic en <code>Más información</code>' : 'Click <code>More info</code>' !!}</li>
            <li>{!! $isEs ? 'Luego en <code>Ejecutar de todas formas</code>' : 'Then click <code>Run anyway</code>' !!}</li>
          </ol>
        </div>
        <div class="reassure">{{ $isEs ? '✓ El servicio es de código abierto, sin anuncios y no recopila datos. Solo permite que tu navegador reproduzca el contenido.' : '✓ The service is open-source, ad-free and collects no data. It only lets your browser play the content.' }}</div>
      </div>
    </div>
  </div>

  {{-- 2 · App YamboTV (Android) + VPN --}}
  <div class="card">
    <h2><span class="num">2</span> {{ $isEs ? 'App YamboTV · Android' : 'YamboTV App · Android' }}</h2>
    <p>{{ $isEs ? 'La app nativa para tu teléfono, tablet y Android TV. La mejor experiencia en pantalla grande, sin instalar nada más.' : 'The native app for your phone, tablet and Android TV. The best big-screen experience, nothing else to install.' }}</p>
    <div class="btn-row">
      <a href="{{ $apk }}" class="cta-btn" download>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>{{ $isEs ? 'Descargar APK' : 'Download APK' }}
      </a>
    </div>
    <input type="checkbox" id="g2" class="acc-input">
    <label for="g2" class="toggle-row">
      <span class="t-label">{{ $isEs ? 'Ver guía de instalación + permisos de Android' : 'Show install guide + Android permissions' }}</span>
      <span class="switch"><span class="track"></span><span class="knob"></span></span>
    </label>
    <div class="acc-content">
      <div class="guide">
        <ol>
          <li>{{ $isEs ? 'Descarga el archivo .apk en tu dispositivo.' : 'Download the .apk file to your device.' }}</li>
          <li>{!! $isEs ? 'Abre el archivo descargado (desde la notificación o la app Archivos).' : 'Open the downloaded file (from the notification or the Files app).' !!}</li>
          <li>{{ $isEs ? 'Aparecerán uno o dos avisos de seguridad — mira los recuadros de abajo.' : 'One or two security prompts will appear — see the boxes below.' }}</li>
          <li>{{ $isEs ? 'Pulsa Instalar, espera, y abre la app.' : 'Tap Install, wait, and open the app.' }}</li>
        </ol>
        <div class="dialog">
          <div class="dlg-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            {{ $isEs ? 'Origen desconocido' : 'Unknown source' }}
          </div>
          {{ $isEs
            ? 'Android pide permiso para instalar apps fuera de Play Store. Es normal al instalar un APK directo:'
            : 'Android asks permission to install apps from outside the Play Store. This is normal for a direct APK:' }}
          <ol class="steps">
            <li>{!! $isEs ? 'Pulsa <code>Configuración</code> en el aviso' : 'Tap <code>Settings</code> in the prompt' !!}</li>
            <li>{!! $isEs ? 'Activa <code>Permitir de esta fuente</code> y vuelve atrás' : 'Enable <code>Allow from this source</code> and go back' !!}</li>
          </ol>
        </div>
        <div class="dialog">
          <div class="dlg-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            {{ $isEs ? 'Play Protect: “app no reconocida”' : 'Play Protect: “unrecognized app”' }}
          </div>
          {{ $isEs
            ? 'Google Play Protect puede avisar porque la app no se descargó de Play Store. No significa que sea dañina:'
            : 'Google Play Protect may warn because the app was not downloaded from the Play Store. It does not mean it is harmful:' }}
          <ol class="steps">
            <li>{!! $isEs ? 'Pulsa <code>Más detalles</code> y luego <code>Instalar de todas formas</code>' : 'Tap <code>More details</code> then <code>Install anyway</code>' !!}</li>
          </ol>
        </div>
        <div class="reassure">{{ $isEs ? '✓ El APK incluye el reproductor completo: en Android NO necesitas el servicio de PC ni nada extra.' : '✓ The APK includes the full player: on Android you do NOT need the PC service or anything extra.' }}</div>
      </div>
    </div>

    {{-- VPN: solo Android, dentro de esta sección --}}
    <div class="vpn-sub">
      <h3>{{ $isEs ? 'VPN Yammbo · opcional (solo Android)' : 'Yammbo VPN · optional (Android only)' }}</h3>
      <p>{{ $isEs ? 'Si tu operador o país bloquea algún contenido, instala la VPN (también es un APK) para un acceso sin límites y mayor privacidad. Se instala igual que la app de arriba.' : 'If your provider or country blocks some content, install the VPN (also an APK) for unrestricted access and more privacy. Install it the same way as the app above.' }}</p>
      <div class="btn-row" style="text-align:left">
        <a href="{{ $vpn }}" class="cta-btn" download>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>{{ $isEs ? 'Descargar VPN' : 'Download VPN' }}
        </a>
        <a href="/app-tv/vpn-info" class="cta-btn ghost" style="margin-left:8px">{{ $isEs ? 'Más info' : 'More info' }}</a>
      </div>
    </div>
  </div>

  <a href="javascript:history.back()" class="back">{{ $isEs ? '← Volver' : '← Back' }}</a>
</div>
</body>
</html>

@php
    $isEs = str_starts_with(app()->getLocale(), 'es');

    // Los APK viven en GitHub Releases, no en el VPS.
    // Estas URLs "latest" NO cambian al publicar una version nueva.
    $apkTv     = 'https://github.com/yammbocom/yammbo-androidtv-releases/releases/latest/download/YamboTV.apk';
    $apkMobile = '/download/movil';   // el asset se llama YammboMobile.apk, no YamboTV.apk
    $svc       = '/download/YammboTV-Service-Setup-v4.exe';

    // Codigos de la app Downloader (go.aftvnews.com). Al rellenarlos, la web
    // los muestra sola; mientras esten vacios se ensena solo la direccion corta.
    $codeTv     = '4874406';   // go.aftvnews.com -> https://tv.yammbo.com/tv
    $codeMobile = null;
    $codeVpn    = '5916443';   // go.aftvnews.com -> https://edge-v5k8r3.pages.dev/v
    $vpn       = '/download/VPN-Yammbo.apk';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<meta name="color-scheme" content="dark">
<title>{{ $isEs ? 'Descargar Yammbo Tv' : 'Download Yammbo Tv' }} — Yammbo Tv</title>
<meta name="description" content="{{ $isEs ? 'Descarga Yammbo Tv para tu televisor o tu telefono Android.' : 'Download Yammbo Tv for your TV or Android phone.' }}">
@php
    $ogImg = file_exists(public_path('images/og-install.jpg'))
        ? '/images/og-install.jpg'
        : (file_exists(public_path('images/og-install.png')) ? '/images/og-install.png' : '/images/yambo-icon.png');
    $ogTitle = $isEs ? 'Instala Yammbo Tv en tu televisor' : 'Install Yammbo Tv on your TV';
    $ogDesc  = $isEs
        ? 'Peliculas, series y TV en vivo. Instalala en tu Fire TV Stick, TV Box o Smart TV con el codigo 4874406 en Downloader, o descargala para Android.'
        : 'Movies, series and live TV. Install it on your Fire TV Stick, TV Box or Smart TV with code 4874406 in Downloader, or download it for Android.';
@endphp
<meta property="og:type" content="website">
<meta property="og:site_name" content="Yammbo Tv">
<meta property="og:locale" content="{{ $isEs ? 'es_ES' : 'en_US' }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:url" content="{{ url('/install') }}">
<meta property="og:image" content="{{ url($ogImg) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<meta name="twitter:image" content="{{ url($ogImg) }}">
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    -webkit-font-smoothing:antialiased;min-height:100vh}
  a{color:inherit}

  /* Halo rojo de fondo, igual que la landing */
  .glow{position:fixed;inset:0;pointer-events:none;z-index:0;
    background:radial-gradient(900px 480px at 50% -8%,rgba(229,9,20,.20),transparent 60%)}

  .wrap{position:relative;z-index:1;max-width:1080px;margin:0 auto;
    padding:clamp(28px,5vw,56px) clamp(16px,4vw,32px) 80px}

  header{text-align:center;margin-bottom:clamp(28px,4vw,44px)}
  header img{height:44px;width:auto;margin-bottom:22px}
  h1{font-size:clamp(28px,5vw,46px);font-weight:800;letter-spacing:-.03em;margin:0 0 12px;line-height:1.1}
  .sub{color:#b8b8bd;font-size:clamp(15px,2.2vw,17px);line-height:1.6;margin:0 auto;max-width:560px}
  .chips{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:20px;padding:0;list-style:none}
  .chips a{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:999px;
    background:#141416;border:1px solid #2a2a30;color:#d4d4d9;font-size:13px;font-weight:600;
    text-decoration:none;transition:border-color .15s ease,color .15s ease}
  .chips a:hover{border-color:#E50914;color:#fff}
  .chips svg{width:15px;height:15px;stroke:#E50914;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

  /* Tarjetas base */
  .card{position:relative;background:linear-gradient(160deg,#141416 0%,#0d0d0f 100%);
    border:1px solid #26262b;border-radius:20px;padding:clamp(22px,3vw,32px)}
  .ico{width:52px;height:52px;border-radius:14px;background:#1c1c20;display:flex;align-items:center;
    justify-content:center;margin-bottom:18px;flex:0 0 auto}
  .ico svg{width:27px;height:27px;stroke:#E50914;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
  .ico.solid svg{fill:#E50914;stroke:none}
  .card h2{margin:0 0 6px;font-size:clamp(20px,2.4vw,24px);font-weight:750;letter-spacing:-.01em}
  .for{color:#8b8b93;font-size:13.5px;line-height:1.55;margin:0 0 18px}
  .badge{position:absolute;top:18px;right:18px;background:#E50914;color:#fff;font-size:10.5px;font-weight:800;
    letter-spacing:.09em;text-transform:uppercase;padding:5px 10px;border-radius:999px}

  /* Tarjeta de TV: destacada, a todo el ancho */
  .tv{border-color:rgba(229,9,20,.45);margin-bottom:18px;scroll-margin-top:16px;
    box-shadow:0 0 0 1px rgba(229,9,20,.12),0 18px 60px -24px rgba(229,9,20,.55)}
  .tv-grid{display:grid;grid-template-columns:1fr;gap:26px}
  .tvlead{margin:0 0 18px;color:#c9c9cf;font-size:14.5px;line-height:1.6;max-width:46ch}
  .tvlead b{color:#fff}
  .codebox{background:#0b0b0d;border:1px solid rgba(229,9,20,.4);border-radius:14px;
    padding:18px;text-align:center}
  .code-label{color:#8b8b93;font-size:11px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}
  .code-num{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:clamp(30px,5vw,42px);
    font-weight:800;color:#E50914;letter-spacing:.14em;line-height:1}
  .code-alt{color:#8b8b93;font-size:12.5px;margin-top:10px}
  .code-alt b{color:#d4d4d9;font-weight:600}
  .tv-steps{background:#0b0b0d;border:1px solid #1f1f24;border-radius:16px;padding:22px 22px 10px}
  .tv-steps h3{margin:0 0 16px;font-size:13px;font-weight:700;color:#8b8b93;text-transform:uppercase;letter-spacing:.09em}
  .steps{margin:0;padding:0;list-style:none;counter-reset:st}
  .steps li{counter-increment:st;position:relative;padding-left:40px;margin-bottom:16px;
    color:#b0b0b8;font-size:14px;line-height:1.55;min-height:26px}
  .steps li::before{content:counter(st);position:absolute;left:0;top:-1px;width:26px;height:26px;
    border-radius:50%;background:#E50914;color:#fff;font-size:12.5px;font-weight:800;
    display:flex;align-items:center;justify-content:center}
  .steps b{color:#fff;font-weight:650}
  .meta{color:#6e6e77;font-size:12px;margin:12px 0 0}

  /* Movil + Windows */
  .apps{display:grid;grid-template-columns:1fr;gap:18px;margin-bottom:18px}
  .app{display:flex;flex-direction:column;transition:border-color .2s ease,transform .2s ease;scroll-margin-top:16px}
  .app:hover{border-color:#3a3a42;transform:translateY(-2px)}
  .app-head{display:flex;gap:16px;align-items:center;margin-bottom:18px}
  .app-head .ico{margin:0}
  .app-head .for{margin:0}
  .feat{list-style:none;margin:0 0 22px;padding:0}
  .feat li{color:#c9c9cf;font-size:14px;line-height:1.5;padding-left:20px;position:relative;margin-bottom:8px}
  .feat li::before{content:"";position:absolute;left:0;top:7px;width:7px;height:7px;border-radius:50%;background:#E50914}
  .app .cta{margin-top:auto}
  .app .meta{text-align:center}

  .btn{display:flex;align-items:center;justify-content:center;gap:9px;background:#E50914;color:#fff;
    text-decoration:none;font-weight:700;font-size:15px;padding:14px 22px;border-radius:12px;min-height:48px;
    transition:background .15s ease}
  .btn:hover{background:#c8070f}
  .btn:focus-visible,.chips a:focus-visible{outline:2px solid #fff;outline-offset:2px}
  .btn.ghost{background:#1c1c20;border:1px solid #303038;font-weight:600;font-size:14px}
  .btn.ghost:hover{background:#26262c}
  .btn svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

  /* Ayuda: guia de APK/EXE + problemas de carga */
  .help{display:grid;grid-template-columns:1fr;gap:18px}
  .help .card{background:#0e0e10;border-color:#212126}
  .help h3{margin:0 0 18px;font-size:18px;font-weight:750}
  .help p{margin:0 0 16px;color:#8b8b93;font-size:14px;line-height:1.6}
  .help p b{color:#d4d4d9}
  .extra{display:flex;flex-direction:column}
  .extra .btn{margin-top:auto}
  .vpncode{background:#0b0b0d;border:1px solid #26262b;border-radius:12px;padding:12px 14px;
    text-align:center;margin:0 0 14px}
  .vpncode .code-num{font-size:26px;color:#fff}

  footer{text-align:center;margin-top:44px;color:#5c5c66;font-size:12.5px;line-height:1.7}
  footer a{color:#9a9aa3;text-decoration:none}
  footer a:hover{color:#E50914}

  /* Tablet: movil y Windows lado a lado */
  @media (min-width:680px){
    .apps{grid-template-columns:1fr 1fr}
  }
  /* Escritorio: TV en dos columnas, ayuda en dos columnas */
  @media (min-width:900px){
    .tv-grid{grid-template-columns:minmax(0,1fr) minmax(0,1.1fr);gap:36px;align-items:center}
    .help{grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)}
  }
  @media (max-width:480px){
    .badge{position:static;display:inline-block;margin-bottom:14px}
    .tv-steps{padding:18px 16px 6px}
  }
  @media (prefers-reduced-motion:reduce){
    .app,.btn,.chips a{transition:none}
    .app:hover{transform:none}
  }
</style>
</head>
<body>
<div class="glow"></div>
<div class="wrap">

  <header>
    <img src="/images/yambo-logo.png" alt="Yammbo Tv" width="147" height="44"
         onerror="this.onerror=null;this.src='/images/yambo-icon.png'">
    <h1>{{ $isEs ? 'Descarga Yammbo Tv' : 'Download Yammbo Tv' }}</h1>
    <p class="sub">
      {{ $isEs
        ? 'Elige la version para tu dispositivo. Peliculas, series, anime y TV en vivo, donde quieras.'
        : 'Pick the version for your device. Movies, series, anime and live TV, anywhere.' }}
    </p>
    <ul class="chips" aria-label="{{ $isEs ? 'Ir a tu dispositivo' : 'Jump to your device' }}">
      <li><a href="#tv"><svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>{{ $isEs ? 'Televisor' : 'TV' }}</a></li>
      <li><a href="#movil"><svg viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/></svg>{{ $isEs ? 'Movil' : 'Mobile' }}</a></li>
      <li><a href="#windows"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M7 20h10"/></svg>Windows</a></li>
    </ul>
  </header>

  {{-- App de TELEVISORES --}}
  <section class="card tv" id="tv">
    <span class="badge">{{ $isEs ? 'Para tu TV' : 'For your TV' }}</span>
    <div class="tv-grid">
      <div>
        <div class="ico">
          <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <h2>{{ $isEs ? 'Yammbo Tv para Televisores' : 'Yammbo Tv for TV' }}</h2>
        <p class="for">Android TV · Google TV · Fire TV · TV Box</p>
        <p class="tvlead">
          {!! $isEs
            ? 'Se instala <b>desde el propio televisor</b>, con la app gratuita Downloader. No necesitas ordenador ni memoria USB.'
            : 'Install it <b>from the TV itself</b> with the free Downloader app. No computer or USB drive needed.' !!}
        </p>
        <div class="codebox">
          <div class="code-label">{{ $isEs ? 'Tu codigo de instalacion' : 'Your install code' }}</div>
          <div class="code-num">{{ $codeTv }}</div>
          <div class="code-alt">{{ $isEs ? 'o escribe' : 'or type' }} <b>tv.yammbo.com/tv</b></div>
        </div>
        <p class="meta">Android TV 5.0+</p>
      </div>

      <div class="tv-steps">
        <h3>{{ $isEs ? 'Paso a paso' : 'Step by step' }}</h3>
        <ol class="steps">
          <li>{!! $isEs
            ? 'En tu televisor, busca <b>Downloader</b> en la tienda de apps (Google Play o Amazon Appstore) e instalala. Es gratis.'
            : 'On your TV, search for <b>Downloader</b> in the app store (Google Play or Amazon Appstore) and install it. It is free.' !!}</li>
          <li>{!! $isEs
            ? 'Abre Downloader y escribe el codigo <b>' . $codeTv . '</b> con el mando. Pulsa <b>Go</b>.'
            : 'Open Downloader, type the code <b>' . $codeTv . '</b> with your remote and press <b>Go</b>.' !!}</li>
          <li>{!! $isEs
            ? 'Espera a que descargue y pulsa <b>Instalar</b>. Si te pide permiso para instalar apps, aceptalo: es normal.'
            : 'Wait for the download and press <b>Install</b>. If it asks permission to install apps, allow it: that is normal.' !!}</li>
          <li>{!! $isEs
            ? 'Abre Yammbo Tv y <b>escanea el codigo QR</b> con tu telefono para iniciar sesion.'
            : 'Open Yammbo Tv and <b>scan the QR code</b> with your phone to sign in.' !!}</li>
        </ol>
      </div>
    </div>
  </section>

  <div class="apps">

    {{-- App MOVIL --}}
    <section class="card app" id="movil">
      <div class="app-head">
        <div class="ico">
          <svg viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
        </div>
        <div>
          <h2>{{ $isEs ? 'Yammbo Tv para Movil' : 'Yammbo Tv for Mobile' }}</h2>
          <p class="for">{{ $isEs ? 'Telefonos y tabletas Android' : 'Android phones and tablets' }}</p>
        </div>
      </div>
      <ul class="feat">
        <li>{{ $isEs ? 'Llevalo contigo a cualquier parte' : 'Take it anywhere' }}</li>
        <li>{{ $isEs ? 'Misma cuenta que en tu televisor' : 'Same account as your TV' }}</li>
        <li>{{ $isEs ? 'Controles pensados para pantalla tactil' : 'Touch-friendly controls' }}</li>
      </ul>
      <div class="cta">
        <a class="btn" href="{{ $apkMobile }}" rel="noopener">
          <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
          {{ $isEs ? 'Descargar para Movil' : 'Download for Mobile' }}
        </a>
        <p class="meta">APK · {{ $isEs ? 'Android 5.0 o superior' : 'Android 5.0+' }}</p>
      </div>
    </section>

    {{-- App de WINDOWS --}}
    <section class="card app" id="windows">
      <div class="app-head">
        <div class="ico solid">
          <svg viewBox="0 0 24 24"><path d="M3 6.5l7-1v6H3v-5zM11 5.2l10-1.4v7.7H11V5.2zM3 12.5h7v6l-7-1v-5zM11 12.5h10v7.7l-10-1.4v-6.3z"/></svg>
        </div>
        <div>
          <h2>{{ $isEs ? 'Yammbo Tv para Windows' : 'Yammbo Tv for Windows' }}</h2>
          <p class="for">{{ $isEs ? 'Ordenadores con Windows 10 y 11' : 'Windows 10 and 11 computers' }}</p>
        </div>
      </div>
      <ul class="feat">
        <li>{{ $isEs ? 'Vela en pantalla grande sin televisor' : 'Watch on a big screen, no TV needed' }}</li>
        <li>{{ $isEs ? 'Misma cuenta que en tus otros dispositivos' : 'Same account as your other devices' }}</li>
        <li>{{ $isEs ? 'Instalador guiado, sin complicaciones' : 'Guided installer, no hassle' }}</li>
      </ul>
      <div class="cta">
        <a class="btn" href="{{ $svc }}">
          <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
          {{ $isEs ? 'Descargar para Windows' : 'Download for Windows' }}
        </a>
        <p class="meta">{{ $isEs ? 'Instalador .exe · Windows 10 o superior' : '.exe installer · Windows 10+' }}</p>
      </div>
    </section>

  </div>

  <div class="help">
    {{-- Como instalar (movil y Windows; la TV ya tiene sus pasos) --}}
    <section class="card">
      <h3>{{ $isEs ? 'Como instalarla en movil o Windows' : 'How to install on mobile or Windows' }}</h3>
      <ol class="steps">
        <li>{{ $isEs
          ? 'Descarga el archivo con el boton de tu dispositivo.'
          : 'Download the file with the button for your device.' }}</li>
        <li>{!! $isEs
          ? 'Abrelo. Si te avisa de <b>origenes desconocidos</b> o de que Windows protegio tu equipo, permite la instalacion: es normal al instalar fuera de la tienda.'
          : 'Open it. If it warns about <b>unknown sources</b> or that Windows protected your PC, allow the install: that is normal outside the store.' !!}</li>
        <li>{!! $isEs
          ? 'Abre Yammbo Tv e <b>inicia sesion</b> con tu cuenta.'
          : 'Open Yammbo Tv and <b>sign in</b> with your account.' !!}</li>
      </ol>
    </section>

    {{-- Extras --}}
    <section class="card extra">
      <h3>{{ $isEs ? 'No carga el contenido?' : 'Content not loading?' }}</h3>
      <p>{!! $isEs
        ? 'Algunos operadores bloquean el servidor de video. Cambia el DNS de tu red a <b>1.1.1.1</b> y <b>8.8.8.8</b>, o usa esta VPN una vez.'
        : 'Some carriers block the video server. Set your network DNS to <b>1.1.1.1</b> and <b>8.8.8.8</b>, or use this VPN once.' !!}</p>
      <div class="vpncode">
        <div class="code-label">{{ $isEs ? 'En tu TV, codigo para Downloader' : 'On your TV, Downloader code' }}</div>
        <div class="code-num">{{ $codeVpn }}</div>
      </div>
      <a class="btn ghost" href="{{ $vpn }}">{{ $isEs ? 'Descargar VPN' : 'Download VPN' }}</a>
    </section>
  </div>

  <footer>
    {{ $isEs ? 'Ya tienes cuenta?' : 'Already have an account?' }}
    <a href="/mi-suscripcion">{{ $isEs ? 'Gestiona tu suscripcion' : 'Manage your subscription' }}</a>
    <br>
    &copy; {{ date('Y') }} Yammbo Tv
  </footer>

</div>
</body>
</html>

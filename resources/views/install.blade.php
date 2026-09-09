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

  .wrap{position:relative;z-index:1;max-width:1040px;margin:0 auto;padding:40px 20px 90px}

  header{text-align:center;margin-bottom:38px}
  header img{height:44px;width:auto;margin-bottom:22px}
  h1{font-size:clamp(28px,5vw,42px);font-weight:800;letter-spacing:-.03em;margin:0 0 12px;line-height:1.1}
  .sub{color:#b8b8bd;font-size:clamp(15px,2.2vw,17px);line-height:1.6;margin:0 auto;max-width:560px}

  /* Tarjetas de descarga */
  .apps{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px;margin-bottom:34px}
  .app{position:relative;background:linear-gradient(160deg,#141416 0%,#0d0d0f 100%);
    border:1px solid #26262b;border-radius:20px;padding:30px 28px;display:flex;flex-direction:column;
    transition:border-color .2s ease,transform .2s ease}
  .app:hover{border-color:#3a3a42;transform:translateY(-2px)}
  .app.primary{border-color:rgba(229,9,20,.45);box-shadow:0 0 0 1px rgba(229,9,20,.12),0 18px 50px -20px rgba(229,9,20,.5)}
  .badge{position:absolute;top:16px;right:16px;background:#E50914;color:#fff;font-size:10.5px;font-weight:800;
    letter-spacing:.09em;text-transform:uppercase;padding:5px 10px;border-radius:999px}
  .ico{width:52px;height:52px;border-radius:14px;background:#1c1c20;display:flex;align-items:center;
    justify-content:center;margin-bottom:18px}
  .ico svg{width:27px;height:27px;stroke:#E50914;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
  .app:nth-child(3) .ico svg{fill:#E50914;stroke:none}
  .app h2{margin:0 0 7px;font-size:21px;font-weight:750;letter-spacing:-.01em}
  .app .for{color:#8b8b93;font-size:13.5px;line-height:1.55;margin:0 0 18px;flex:1}
  .feat{list-style:none;margin:0 0 22px;padding:0}
  .feat li{color:#c9c9cf;font-size:13.5px;line-height:1.5;padding-left:20px;position:relative;margin-bottom:7px}
  .feat li::before{content:"";position:absolute;left:0;top:7px;width:7px;height:7px;border-radius:50%;background:#E50914}

  /* Tarjeta de TV: instalacion por codigo */
  .tvlead{margin:0 0 16px;color:#c9c9cf;font-size:13.5px;line-height:1.6}
  .codebox{background:#0b0b0d;border:1px solid rgba(229,9,20,.4);border-radius:14px;
    padding:16px 18px;text-align:center;margin-bottom:18px}
  .code-label{color:#8b8b93;font-size:11px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:7px}
  .code-num{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:34px;
    font-weight:800;color:#E50914;letter-spacing:.14em;line-height:1}
  .tvsteps{margin:0 0 14px;padding:0 0 0 4px;list-style:none;counter-reset:tv}
  .tvsteps li{counter-increment:tv;position:relative;padding-left:31px;margin-bottom:11px;
    color:#b0b0b8;font-size:13.5px;line-height:1.55}
  .tvsteps li::before{content:counter(tv);position:absolute;left:0;top:0;width:21px;height:21px;
    border-radius:50%;background:#26262c;color:#fff;font-size:11.5px;font-weight:800;
    display:flex;align-items:center;justify-content:center}
  .tvsteps b{color:#fff;font-weight:650}
  .btn{display:flex;align-items:center;justify-content:center;gap:9px;background:#E50914;color:#fff;
    text-decoration:none;font-weight:700;font-size:15px;padding:14px 22px;border-radius:12px;
    transition:background .15s ease}
  .btn:hover{background:#c8070f}
  .btn.ghost{background:#1c1c20;border:1px solid #303038;font-weight:600;font-size:14px}
  .btn.ghost:hover{background:#26262c}
  .btn svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .meta{text-align:center;color:#6e6e77;font-size:11.5px;margin-top:11px}

  /* Guia de instalacion */
  .guide{background:#0e0e10;border:1px solid #212126;border-radius:20px;padding:30px 28px;margin-bottom:18px}
  .guide h3{margin:0 0 22px;font-size:18px;font-weight:750}
  .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:22px}
  .step{display:flex;gap:13px}
  .num{flex:0 0 auto;width:27px;height:27px;border-radius:50%;background:#E50914;color:#fff;
    font-size:13.5px;font-weight:800;display:flex;align-items:center;justify-content:center}
  .step p{margin:0;color:#b0b0b8;font-size:13.5px;line-height:1.6}
  .step b{color:#fff;font-weight:650}

  /* Extras */
  .extras{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
  .extra{background:#0e0e10;border:1px solid #212126;border-radius:16px;padding:20px 22px}
  .extra h4{margin:0 0 6px;font-size:15px;font-weight:700}
  .extra p{margin:0 0 14px;color:#8b8b93;font-size:13px;line-height:1.55}

  /* Instalar con Downloader (Fire TV / Android TV) */
  .dl{background:linear-gradient(160deg,#141416 0%,#0d0d0f 100%);border:1px solid #26262b;
    border-radius:20px;padding:30px 28px;margin-bottom:18px}
  .dl h3{margin:0 0 6px;font-size:18px;font-weight:750}
  .dl .lead{margin:0 0 22px;color:#8b8b93;font-size:13.5px;line-height:1.6}
  .codes{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
  .code{background:#0b0b0d;border:1px solid #26262b;border-radius:14px;padding:18px 20px;text-align:center}
  .code .what{color:#8b8b93;font-size:12px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}
  .code .val{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:22px;
    font-weight:700;color:#fff;letter-spacing:.04em;word-break:break-all}
  .code .alt{color:#6e6e77;font-size:12px;margin-top:9px}
  .code .val.big{font-size:30px;color:#E50914;letter-spacing:.12em}
  footer{text-align:center;margin-top:44px;color:#5c5c66;font-size:12.5px;line-height:1.7}
  footer a{color:#9a9aa3;text-decoration:none}
  footer a:hover{color:#E50914}

  @media (max-width:520px){
    .app{padding:26px 22px}
    .guide{padding:24px 20px}
  }
</style>
</head>
<body>
<div class="glow"></div>
<div class="wrap">

  <header>
    <img src="/images/yambo-logo.png" alt="Yammbo Tv"
         onerror="this.onerror=null;this.src='/images/yambo-icon.png'">
    <h1>{{ $isEs ? 'Descarga Yammbo Tv' : 'Download Yammbo Tv' }}</h1>
    <p class="sub">
      {{ $isEs
        ? 'Elige la version para tu dispositivo. Peliculas, series, anime y TV en vivo, donde quieras.'
        : 'Pick the version for your device. Movies, series, anime and live TV, anywhere.' }}
    </p>
  </header>

  <div class="apps">

    {{-- App de TELEVISORES --}}
    <div class="app primary">
      <span class="badge">{{ $isEs ? 'Para tu TV' : 'For your TV' }}</span>
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
      </div>

      <ol class="tvsteps">
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

      <p class="meta">{{ $isEs ? 'o escribe' : 'or type' }} tv.yammbo.com/tv · Android TV 5.0+</p>
    </div>

    {{-- App MOVIL --}}
    <div class="app">
      <div class="ico">
        <svg viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
      </div>
      <h2>{{ $isEs ? 'Yammbo Tv para Movil' : 'Yammbo Tv for Mobile' }}</h2>
      <p class="for">{{ $isEs ? 'Telefonos y tabletas Android' : 'Android phones and tablets' }}</p>
      <ul class="feat">
        <li>{{ $isEs ? 'Llevalo contigo a cualquier parte' : 'Take it anywhere' }}</li>
        <li>{{ $isEs ? 'Misma cuenta que en tu televisor' : 'Same account as your TV' }}</li>
        <li>{{ $isEs ? 'Controles pensados para pantalla tactil' : 'Touch-friendly controls' }}</li>
      </ul>
      <a class="btn" href="{{ $apkMobile }}" rel="noopener">
        <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
        {{ $isEs ? 'Descargar para Movil' : 'Download for Mobile' }}
      </a>
      <p class="meta">APK · {{ $isEs ? 'Android 5.0 o superior' : 'Android 5.0+' }}</p>
    </div>

    {{-- App de WINDOWS --}}
    <div class="app">
      <div class="ico">
        <svg viewBox="0 0 24 24"><path d="M3 6.5l7-1v6H3v-5zM11 5.2l10-1.4v7.7H11V5.2zM3 12.5h7v6l-7-1v-5zM11 12.5h10v7.7l-10-1.4v-6.3z"/></svg>
      </div>
      <h2>{{ $isEs ? 'Yammbo Tv para Windows' : 'Yammbo Tv for Windows' }}</h2>
      <p class="for">{{ $isEs ? 'Ordenadores con Windows 10 y 11' : 'Windows 10 and 11 computers' }}</p>
      <ul class="feat">
        <li>{{ $isEs ? 'Vela en pantalla grande sin televisor' : 'Watch on a big screen, no TV needed' }}</li>
        <li>{{ $isEs ? 'Misma cuenta que en tus otros dispositivos' : 'Same account as your other devices' }}</li>
        <li>{{ $isEs ? 'Instalador guiado, sin complicaciones' : 'Guided installer, no hassle' }}</li>
      </ul>
      <a class="btn" href="{{ $svc }}">
        <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
        {{ $isEs ? 'Descargar para Windows' : 'Download for Windows' }}
      </a>
      <p class="meta">{{ $isEs ? 'Instalador .exe · Windows 10 o superior' : '.exe installer · Windows 10+' }}</p>
    </div>

  </div>

  {{-- Como instalar --}}
  <div class="guide">
    <h3>{{ $isEs ? 'Como instalarla' : 'How to install' }}</h3>
    <div class="steps">
      <div class="step">
        <div class="num">1</div>
        <p>{{ $isEs
          ? 'Descarga el archivo pulsando el boton de arriba.'
          : 'Download the file using the button above.' }}</p>
      </div>
      <div class="step">
        <div class="num">2</div>
        <p>{!! $isEs
          ? 'Abrelo. Si te avisa de <b>origenes desconocidos</b>, permite la instalacion: es normal al instalar fuera de la tienda.'
          : 'Open it. If it warns about <b>unknown sources</b>, allow the install: that is normal outside the store.' !!}</p>
      </div>
      <div class="step">
        <div class="num">3</div>
        <p>{!! $isEs
          ? 'Abre Yammbo Tv e <b>inicia sesion</b>. En el televisor solo tienes que escanear el codigo QR con tu telefono.'
          : 'Open Yammbo Tv and <b>sign in</b>. On TV just scan the QR code with your phone.' !!}</p>
      </div>
    </div>
  </div>

  {{-- Extras --}}
  <div class="extras">
    <div class="extra">
      <h4>{{ $isEs ? 'No carga el contenido?' : 'Content not loading?' }}</h4>
      <p>{!! $isEs
        ? 'Algunos operadores bloquean el servidor de video. Cambia el DNS de tu red a <b>1.1.1.1</b> y <b>8.8.8.8</b>, o usa esta VPN una vez.'
        : 'Some carriers block the video server. Set your network DNS to <b>1.1.1.1</b> and <b>8.8.8.8</b>, or use this VPN once.' !!}</p>
      <a class="btn ghost" href="{{ $vpn }}">{{ $isEs ? 'Descargar VPN' : 'Download VPN' }}</a>
    </div>
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

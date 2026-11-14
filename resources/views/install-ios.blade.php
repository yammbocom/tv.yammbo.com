@php
    $isEs = str_starts_with(app()->getLocale(), 'es');
    // Icono de la PWA (180x180, fondo negro): el mismo que enlaza /app para iOS.
    $appIcon = '/app/images/apple-touch-icon.png';
    $ogTitle = $isEs ? 'Yammbo Tv en tu iPhone y iPad' : 'Yammbo Tv on your iPhone and iPad';
    $ogDesc  = $isEs
        ? 'Añade Yammbo Tv a tu pantalla de inicio y ábrelo como una app. Sin App Store y en menos de un minuto.'
        : 'Add Yammbo Tv to your Home Screen and open it like an app. No App Store, in under a minute.';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<meta name="color-scheme" content="dark">
<title>{{ $isEs ? 'Instalar en iPhone y iPad' : 'Install on iPhone and iPad' }} — Yammbo Tv</title>
<meta name="description" content="{{ $ogDesc }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Yammbo Tv">
<meta property="og:locale" content="{{ $isEs ? 'es_ES' : 'en_US' }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<link rel="canonical" href="{{ url('/install/ios') }}">
<meta property="og:url" content="{{ url('/install/ios') }}">
<meta property="og:image" content="{{ url('/images/yambo-icon.png') }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $appIcon }}">
<link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:var(--font);
    -webkit-font-smoothing:antialiased;min-height:100vh}
  a{color:inherit}
  [hidden]{display:none !important}

  .wrap{max-width:760px;margin:0 auto;
    padding:clamp(28px,5vw,56px) clamp(16px,4vw,32px) calc(64px + env(safe-area-inset-bottom))}

  header{text-align:center;margin-bottom:clamp(24px,4vw,36px)}
  .brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:22px;text-decoration:none;
    font-size:22px;font-weight:800;letter-spacing:-.02em}
  .brand img{width:40px;height:40px}
  h1{font-size:clamp(28px,6vw,44px);font-weight:800;letter-spacing:-.03em;margin:0 0 12px;line-height:1.1}
  .sub{color:var(--muted);font-size:clamp(15px,2.2vw,17px);line-height:1.6;margin:0 auto;max-width:540px}
  .perks{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:20px 0 0;padding:0;list-style:none}
  .perks li{padding:7px 13px;border-radius:999px;background:var(--soft);border:1px solid var(--line);
    color:var(--fg);font-size:13px;font-weight:600}

  .card{background:var(--soft);border:1px solid var(--line);border-radius:var(--radius);
    padding:clamp(20px,3vw,30px);margin-bottom:18px}
  .card h2{margin:0 0 6px;font-size:clamp(19px,2.4vw,22px);font-weight:750;letter-spacing:-.01em}
  .card .for{color:var(--muted);font-size:14px;line-height:1.55;margin:0 0 18px}

  /* Avisos segun el dispositivo/navegador (los rellena el script) */
  .notice{border-radius:var(--radius);padding:16px 18px;margin-bottom:18px;font-size:14.5px;line-height:1.55;
    background:var(--soft);border:1px solid var(--line-strong);color:var(--fg)}
  .notice b{color:#fff}
  .notice.warn{border-color:var(--accent)}
  .notice.ok{border-color:var(--ok)}
  .notice .btn{margin-top:14px}

  /* Pestanas por navegador */
  .tabs{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 18px;padding:4px;background:#000;border:1px solid var(--line);
    border-radius:12px}
  .tabs button{flex:1 1 0;min-width:72px;min-height:42px;border:0;border-radius:9px;background:transparent;color:var(--muted);
    font:inherit;font-size:14px;font-weight:650;cursor:pointer;padding:8px 10px}
  .tabs button[aria-selected="true"]{background:var(--soft-2);color:#fff;box-shadow:inset 0 0 0 1px var(--line-strong)}
  .tabs button:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
  .tabs .here{display:block;font-size:10.5px;font-weight:700;color:var(--accent-text);margin-top:1px}
  .panel h3{margin:0 0 4px;font-size:16px;font-weight:750}
  .panel .need{color:var(--muted);font-size:12.5px;margin:0 0 18px}
  /* Sin JS se ven todos los paneles seguidos */
  .js .panel{display:none}
  .js .panel.on{display:block}
  .no-js-sep{border:0;border-top:1px solid var(--line);margin:26px 0}
  .js .no-js-sep{display:none}

  .steps{margin:0;padding:0;list-style:none;counter-reset:st}
  .steps > li{counter-increment:st;position:relative;padding-left:42px;margin-bottom:22px;
    color:var(--muted);font-size:15px;line-height:1.55;min-height:28px}
  .steps > li:last-child{margin-bottom:4px}
  .steps > li::before{content:counter(st);position:absolute;left:0;top:-1px;width:28px;height:28px;
    border-radius:50%;background:var(--accent);color:#fff;font-size:13px;font-weight:800;
    display:flex;align-items:center;justify-content:center}
  .steps b{color:#fff;font-weight:650}
  .steps .btn{margin-top:12px}
  .tip{display:block;margin-top:6px;font-size:13px;color:var(--muted)}

  /* Iconos en linea, dibujados como los de iOS */
  .k{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;margin:0 2px;
    vertical-align:-7px;border-radius:7px;background:var(--soft-2);border:1px solid var(--line-strong)}
  .k svg{width:16px;height:16px;stroke:#4da3ff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .k.dots svg{fill:#4da3ff;stroke:none}

  /* Maqueta de la fila del menu Compartir y del dialogo Anadir */
  .mock{margin-top:12px;border-radius:12px;background:#1c1c1e;border:1px solid #2c2c2e;overflow:hidden;
    font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",var(--font);max-width:340px}
  .mock .row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;color:#fff;font-size:15px}
  .mock .row + .row{border-top:1px solid #2c2c2e}
  .mock .row.dim{color:#8e8e93}
  .mock .row svg{width:20px;height:20px;stroke:#fff;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex:0 0 auto}
  .mock .switch{width:42px;height:26px;border-radius:13px;background:#34c759;position:relative;flex:0 0 auto}
  .mock .switch::after{content:"";position:absolute;top:2px;right:2px;width:22px;height:22px;border-radius:50%;background:#fff}
  .mock .top{display:flex;justify-content:space-between;align-items:center;padding:11px 14px;font-size:15px;color:#4da3ff}
  .mock .top b{color:#fff;font-weight:600}
  .mock .top .add{font-weight:600}
  .mock .app{display:flex;gap:12px;align-items:center;padding:4px 14px 14px}
  .mock .app img{width:52px;height:52px;border-radius:12px}
  .mock .app span{color:#fff;font-size:15px}
  .mock .app small{display:block;color:#8e8e93;font-size:12.5px;margin-top:2px}

  .btn{display:flex;align-items:center;justify-content:center;gap:9px;background:var(--accent);color:#fff;
    text-decoration:none;font-weight:700;font-size:15px;padding:13px 20px;border-radius:12px;min-height:48px;
    border:0;font-family:inherit;cursor:pointer;transition:background .15s ease;width:100%;max-width:340px}
  .btn:hover{background:var(--accent-hover)}
  .btn:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
  .btn.ghost{background:var(--soft-2);border:1px solid var(--line-strong);font-weight:600;font-size:14px}
  .btn.ghost:hover{background:var(--hover)}
  .btn svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

  .after{display:flex;gap:18px;align-items:flex-start}
  .after img{width:64px;height:64px;border-radius:15px;flex:0 0 auto;border:1px solid var(--line)}
  .after ul{margin:0;padding:0 0 0 18px;color:var(--muted);font-size:14.5px;line-height:1.6}
  .after li{margin-bottom:8px}
  .after li b{color:#fff}

  details{border-top:1px solid var(--line);padding:14px 0}
  details:first-of-type{border-top:0;padding-top:0}
  summary{cursor:pointer;font-weight:650;font-size:15px;list-style:none;display:flex;justify-content:space-between;gap:12px}
  summary::-webkit-details-marker{display:none}
  summary::after{content:"+";color:var(--muted);font-weight:400;font-size:20px;line-height:1}
  details[open] summary::after{content:"–"}
  details p{margin:10px 0 0;color:var(--muted);font-size:14px;line-height:1.6}
  details p b{color:#fff}

  footer{text-align:center;margin-top:36px;color:var(--muted);font-size:13px;line-height:1.8}
  footer a{color:var(--fg);font-weight:600}
  @media (max-width:420px){
    .after{flex-direction:column}
  }
  @media (prefers-reduced-motion:reduce){ .btn{transition:none} }
</style>
</head>
<body>
<div class="wrap">

  <header>
    <a class="brand" href="/">
      <img src="/images/yambo-icon.png" alt="" width="40" height="40">
      <span>Yammbo Tv</span>
    </a>
    <h1>{{ $isEs ? 'Yammbo Tv en tu iPhone y iPad' : 'Yammbo Tv on your iPhone and iPad' }}</h1>
    <p class="sub">{{ $isEs
      ? 'No necesitas la App Store: la añades desde el navegador a tu pantalla de inicio y se abre como una app, a pantalla completa. Tardas menos de un minuto.'
      : 'No App Store needed: add it from your browser to your Home Screen and it opens like an app, full screen. It takes less than a minute.' }}</p>
    <ul class="perks">
      <li>{{ $isEs ? 'Icono en tu pantalla de inicio' : 'Icon on your Home Screen' }}</li>
      <li>{{ $isEs ? 'Pantalla completa' : 'Full screen' }}</li>
      <li>{{ $isEs ? 'Siempre actualizada' : 'Always up to date' }}</li>
    </ul>
  </header>

  {{-- Avisos segun donde se abre la pagina (los muestra el script) --}}
  <div class="notice ok" id="n-installed" hidden>
    {!! $isEs ? '<b>Ya la tienes instalada.</b> Estás usando Yammbo Tv desde tu pantalla de inicio.' : '<b>It is already installed.</b> You are using Yammbo Tv from your Home Screen.' !!}
    <a class="btn" href="/app/">{{ $isEs ? 'Ir a Yammbo Tv' : 'Go to Yammbo Tv' }}</a>
  </div>
  <div class="notice" id="n-desktop" hidden>
    {!! $isEs
      ? 'Esta guía es para <b>iPhone y iPad</b>. Ábrela en tu iPhone o iPad: <b>tv.yammbo.com/install/ios</b>.<br>¿Android, televisor o Windows? Están en <a href="/install">todas las apps</a>.'
      : 'This guide is for <b>iPhone and iPad</b>. Open it on your iPhone or iPad: <b>tv.yammbo.com/install/ios</b>.<br>Android, TV or Windows? See <a href="/install">all the apps</a>.' !!}
  </div>
  <div class="notice warn" id="n-inapp" hidden>
    {!! $isEs
      ? 'Estás viendo esta página <b>dentro de otra app</b> (Instagram, Facebook, TikTok…) y desde aquí no se puede instalar. Toca el menú <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> y elige <b>Abrir en el navegador</b>, o copia el enlace y pégalo en Safari.'
      : 'You are viewing this page <b>inside another app</b> (Instagram, Facebook, TikTok…) and it cannot be installed from here. Tap the <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> menu and choose <b>Open in browser</b>, or copy the link and paste it into Safari.' !!}
    <button class="btn ghost js-copy" type="button">{{ $isEs ? 'Copiar enlace' : 'Copy link' }}</button>
  </div>

  <section class="card" aria-labelledby="t-steps">
    <h2 id="t-steps">{{ $isEs ? 'Cómo instalarla' : 'How to install it' }}</h2>
    <p class="for">{{ $isEs
      ? 'Elige el navegador que usas. Lee los pasos y luego toca «Abrir Yammbo Tv»: la app se añade desde ahí, no desde esta página.'
      : 'Pick the browser you use. Read the steps, then tap “Open Yammbo Tv”: the app is added from there, not from this page.' }}</p>

    <div class="tabs" role="tablist" aria-label="{{ $isEs ? 'Navegador' : 'Browser' }}" hidden>
      <button type="button" role="tab" id="tab-safari" aria-controls="p-safari" data-b="safari">Safari<span class="here" hidden>{{ $isEs ? 'el tuyo' : 'yours' }}</span></button>
      <button type="button" role="tab" id="tab-chrome" aria-controls="p-chrome" data-b="chrome">Chrome<span class="here" hidden>{{ $isEs ? 'el tuyo' : 'yours' }}</span></button>
      <button type="button" role="tab" id="tab-edge" aria-controls="p-edge" data-b="edge">Edge<span class="here" hidden>{{ $isEs ? 'el tuyo' : 'yours' }}</span></button>
      <button type="button" role="tab" id="tab-other" aria-controls="p-other" data-b="other">{{ $isEs ? 'Otro' : 'Other' }}<span class="here" hidden>{{ $isEs ? 'el tuyo' : 'yours' }}</span></button>
    </div>

    {{-- SAFARI --}}
    <div class="panel" id="p-safari" role="tabpanel" aria-labelledby="tab-safari">
      <h3>Safari</h3>
      <p class="need">{{ $isEs ? 'El navegador que trae el iPhone.' : 'The browser that comes with the iPhone.' }}</p>
      <ol class="steps">
        <li>{!! $isEs ? 'Toca <b>Abrir Yammbo Tv</b> e <b>inicia sesión</b> con tu cuenta.' : 'Tap <b>Open Yammbo Tv</b> and <b>sign in</b> with your account.' !!}
          <a class="btn" href="/app/">{{ $isEs ? 'Abrir Yammbo Tv' : 'Open Yammbo Tv' }}</a></li>
        <li>{!! $isEs
          ? 'Toca <b>Compartir</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span> en la barra de Safari. Si no lo ves, toca primero <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> y luego <b>Compartir</b>.'
          : 'Tap <b>Share</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span> in the Safari bar. If you do not see it, first tap <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> and then <b>Share</b>.' !!}</li>
        <li>{!! $isEs ? 'Desliza hacia abajo y toca <b>Añadir a pantalla de inicio</b>.' : 'Scroll down and tap <b>Add to Home Screen</b>.' !!}
          <div class="mock" aria-hidden="true">
            <div class="row dim"><span>{{ $isEs ? 'Añadir a favoritos' : 'Add to Favorites' }}</span><svg viewBox="0 0 24 24"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg></div>
            <div class="row"><span>{{ $isEs ? 'Añadir a pantalla de inicio' : 'Add to Home Screen' }}</span><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/></svg></div>
          </div>
          <span class="tip">{!! $isEs
            ? '¿No aparece? Baja hasta el final de la lista, toca <b>Editar acciones</b> y añádela.'
            : 'Not there? Scroll to the end of the list, tap <b>Edit Actions</b> and add it.' !!}</span></li>
        <li>{!! $isEs
          ? 'Deja activado <b>Abrir como app web</b> (si aparece) y toca <b>Añadir</b>. Listo: Yammbo Tv ya está en tu pantalla de inicio.'
          : 'Keep <b>Open as Web App</b> turned on (if shown) and tap <b>Add</b>. Done: Yammbo Tv is on your Home Screen.' !!}
          <div class="mock" aria-hidden="true">
            <div class="top"><span>{{ $isEs ? 'Cancelar' : 'Cancel' }}</span><b>{{ $isEs ? 'Añadir a inicio' : 'Add to Home Screen' }}</b><span class="add">{{ $isEs ? 'Añadir' : 'Add' }}</span></div>
            <div class="app"><img src="{{ $appIcon }}" alt=""><div><span>Yammbo Tv</span><small>tv.yammbo.com</small></div></div>
            <div class="row"><span>{{ $isEs ? 'Abrir como app web' : 'Open as Web App' }}</span><span class="switch"></span></div>
          </div></li>
      </ol>
    </div>
    <hr class="no-js-sep">

    {{-- CHROME --}}
    <div class="panel" id="p-chrome" role="tabpanel" aria-labelledby="tab-chrome">
      <h3>Chrome</h3>
      <p class="need">{{ $isEs ? 'Necesita iOS 16.4 o posterior.' : 'Requires iOS 16.4 or later.' }}</p>
      <ol class="steps">
        <li>{!! $isEs ? 'Toca <b>Abrir Yammbo Tv</b> e <b>inicia sesión</b> con tu cuenta.' : 'Tap <b>Open Yammbo Tv</b> and <b>sign in</b> with your account.' !!}
          <a class="btn" href="/app/">{{ $isEs ? 'Abrir Yammbo Tv' : 'Open Yammbo Tv' }}</a></li>
        <li>{!! $isEs
          ? 'Toca <b>Compartir</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span>, arriba a la derecha en la barra de direcciones. Si no está, toca <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> y luego <b>Compartir</b>.'
          : 'Tap <b>Share</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span>, at the top right of the address bar. If it is not there, tap <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> and then <b>Share</b>.' !!}</li>
        <li>{!! $isEs ? 'Toca <b>Añadir a pantalla de inicio</b> (desliza hacia abajo si no la ves).' : 'Tap <b>Add to Home Screen</b> (scroll down if you do not see it).' !!}</li>
        <li>{!! $isEs ? 'Toca <b>Añadir</b>. Listo.' : 'Tap <b>Add</b>. Done.' !!}</li>
      </ol>
    </div>
    <hr class="no-js-sep">

    {{-- EDGE --}}
    <div class="panel" id="p-edge" role="tabpanel" aria-labelledby="tab-edge">
      <h3>Edge</h3>
      <p class="need">{{ $isEs ? 'Necesita iOS 16.4 o posterior.' : 'Requires iOS 16.4 or later.' }}</p>
      <ol class="steps">
        <li>{!! $isEs ? 'Toca <b>Abrir Yammbo Tv</b> e <b>inicia sesión</b> con tu cuenta.' : 'Tap <b>Open Yammbo Tv</b> and <b>sign in</b> with your account.' !!}
          <a class="btn" href="/app/">{{ $isEs ? 'Abrir Yammbo Tv' : 'Open Yammbo Tv' }}</a></li>
        <li>{!! $isEs
          ? 'Toca el menú <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> y luego <b>Compartir</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span>.'
          : 'Tap the <span class="k dots" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span> menu and then <b>Share</b> <span class="k" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M8 11H6v10h12V11h-2"/></svg></span>.' !!}</li>
        <li>{!! $isEs ? 'Toca <b>Añadir a pantalla de inicio</b>.' : 'Tap <b>Add to Home Screen</b>.' !!}</li>
        <li>{!! $isEs ? 'Toca <b>Añadir</b>. Listo.' : 'Tap <b>Add</b>. Done.' !!}</li>
      </ol>
    </div>
    <hr class="no-js-sep">

    {{-- OTRO NAVEGADOR --}}
    <div class="panel" id="p-other" role="tabpanel" aria-labelledby="tab-other">
      <h3>{{ $isEs ? 'Otro navegador (Brave, Firefox, Opera…)' : 'Another browser (Brave, Firefox, Opera…)' }}</h3>
      <p class="need">{{ $isEs ? 'Casi todos lo permiten desde su menú Compartir.' : 'Most of them allow it from their Share menu.' }}</p>
      <ol class="steps">
        <li>{!! $isEs ? 'Toca <b>Abrir Yammbo Tv</b> e <b>inicia sesión</b> con tu cuenta.' : 'Tap <b>Open Yammbo Tv</b> and <b>sign in</b> with your account.' !!}
          <a class="btn" href="/app/">{{ $isEs ? 'Abrir Yammbo Tv' : 'Open Yammbo Tv' }}</a></li>
        <li>{!! $isEs
          ? 'Busca <b>Compartir</b> en el menú del navegador y toca <b>Añadir a pantalla de inicio</b>.'
          : 'Find <b>Share</b> in the browser menu and tap <b>Add to Home Screen</b>.' !!}</li>
        <li>{!! $isEs
          ? '¿No aparece esa opción? Ábrela en <b>Safari</b>: copia el enlace, pégalo en Safari y sigue los pasos de Safari.'
          : 'Option not there? Use <b>Safari</b>: copy the link, paste it into Safari and follow the Safari steps.' !!}
          <button class="btn ghost js-copy" type="button">{{ $isEs ? 'Copiar enlace' : 'Copy link' }}</button></li>
      </ol>
    </div>
  </section>

  <section class="card" aria-labelledby="t-after">
    <h2 id="t-after">{{ $isEs ? 'Después de instalarla' : 'After installing it' }}</h2>
    <div class="after">
      <img src="{{ $appIcon }}" alt="{{ $isEs ? 'Icono de Yammbo Tv' : 'Yammbo Tv icon' }}" width="64" height="64">
      <ul>
        <li>{!! $isEs ? 'Busca este <b>icono rojo</b> en tu pantalla de inicio y ábrela desde ahí.' : 'Look for this <b>red icon</b> on your Home Screen and open it from there.' !!}</li>
        <li>{!! $isEs
          ? 'Si te pide <b>iniciar sesión</b>, hazlo una vez: la app guarda su propia sesión, aparte de la del navegador.'
          : 'If it asks you to <b>sign in</b>, do it once: the app keeps its own session, separate from the browser.' !!}</li>
        <li>{{ $isEs ? 'Tu biblioteca y tu progreso son los mismos que en tus otros dispositivos.' : 'Your library and progress are the same as on your other devices.' }}</li>
      </ul>
    </div>
  </section>

  <section class="card" aria-labelledby="t-faq">
    <h2 id="t-faq">{{ $isEs ? 'Preguntas frecuentes' : 'FAQ' }}</h2>
    <details>
      <summary>{{ $isEs ? '¿Por qué no está en la App Store?' : 'Why is it not on the App Store?' }}</summary>
      <p>{{ $isEs
        ? 'Yammbo Tv funciona desde el navegador. Al añadirla a tu pantalla de inicio se comporta como una app y siempre tienes la última versión, sin esperar actualizaciones.'
        : 'Yammbo Tv runs in the browser. Added to your Home Screen it behaves like an app and you always get the latest version, without waiting for updates.' }}</p>
    </details>
    <details>
      <summary>{{ $isEs ? '¿Ocupa espacio?' : 'Does it take up space?' }}</summary>
      <p>{{ $isEs ? 'Casi nada: no se descarga ninguna app grande, solo se guarda el acceso.' : 'Almost none: no big app is downloaded, only the shortcut is saved.' }}</p>
    </details>
    <details>
      <summary>{{ $isEs ? '¿Qué versión de iOS necesito?' : 'Which iOS version do I need?' }}</summary>
      <p>{!! $isEs
        ? 'Con <b>Safari</b> sirve cualquier iPhone o iPad actualizado. Con <b>Chrome</b> o <b>Edge</b> hace falta iOS 16.4 o posterior; si tu versión es anterior, usa Safari.'
        : 'With <b>Safari</b> any up-to-date iPhone or iPad works. <b>Chrome</b> or <b>Edge</b> need iOS 16.4 or later; on older versions, use Safari.' !!}</p>
    </details>
    <details>
      <summary>{{ $isEs ? '¿Cómo la quito?' : 'How do I remove it?' }}</summary>
      <p>{{ $isEs ? 'Mantén pulsado el icono de Yammbo Tv en tu pantalla de inicio y elige la opción de eliminar. Tu cuenta no se borra.' : 'Press and hold the Yammbo Tv icon on your Home Screen and choose the remove option. Your account is not deleted.' }}</p>
    </details>
  </section>

  <footer>
    {{ $isEs ? '¿Android, televisor o Windows?' : 'Android, TV or Windows?' }}
    <a href="/install">{{ $isEs ? 'Ver todas las apps' : 'See all the apps' }}</a>
    <br>&copy; {{ date('Y') }} Yammbo Tv
  </footer>

</div>
<script>
(function () {
  var d = document, ua = navigator.userAgent || '';
  var isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  var standalone = navigator.standalone === true ||
    (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches);
  var inApp = /Instagram|FBAN|FBAV|FB_IAB|TikTok|musical_ly|BytedanceWebview|Line\/|Snapchat|Twitter/i.test(ua);
  var browser = /CriOS/.test(ua) ? 'chrome' : /EdgiOS/.test(ua) ? 'edge'
    : /FxiOS|OPiOS|OPT\/|GSA\//.test(ua) ? 'other' : 'safari';

  function show(id) { var el = d.getElementById(id); if (el) el.hidden = false; }
  if (standalone) show('n-installed');
  else if (!isIOS) show('n-desktop');
  else if (inApp) show('n-inapp');

  // Pestanas: con JS se ve un solo navegador, el que se detecta por defecto.
  d.documentElement.className += ' js';
  var tabs = d.querySelector('.tabs'), btns = tabs.querySelectorAll('button');
  tabs.hidden = false;
  function select(b) {
    for (var i = 0; i < btns.length; i++) {
      var on = btns[i].getAttribute('data-b') === b;
      btns[i].setAttribute('aria-selected', on ? 'true' : 'false');
      btns[i].tabIndex = on ? 0 : -1;
      d.getElementById('p-' + btns[i].getAttribute('data-b')).classList.toggle('on', on);
    }
  }
  for (var i = 0; i < btns.length; i++) {
    btns[i].addEventListener('click', function () { select(this.getAttribute('data-b')); });
    if (isIOS && !inApp && btns[i].getAttribute('data-b') === browser) btns[i].querySelector('.here').hidden = false;
  }
  tabs.addEventListener('keydown', function (e) {
    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
    var list = Array.prototype.slice.call(btns), cur = list.indexOf(d.activeElement);
    if (cur < 0) return;
    var next = list[(cur + (e.key === 'ArrowRight' ? 1 : list.length - 1)) % list.length];
    next.focus(); select(next.getAttribute('data-b'));
  });
  select(isIOS && !inApp ? browser : 'safari');

  // Copiar enlace (para pasar de una app o navegador a Safari)
  var copied = @json($isEs ? 'Enlace copiado' : 'Link copied');
  var copies = d.querySelectorAll('.js-copy');
  for (var j = 0; j < copies.length; j++) {
    copies[j].addEventListener('click', function () {
      var btn = this, url = location.origin + '/install/ios', label = btn.textContent;
      function done() { btn.textContent = copied; setTimeout(function () { btn.textContent = label; }, 2000); }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done, function () { window.prompt('', url); });
      } else { window.prompt('', url); }
    });
  }
})();
</script>
</body>
</html>

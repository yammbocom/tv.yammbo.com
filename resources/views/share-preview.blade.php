@php
    $isEs = str_starts_with(app()->getLocale(), 'es');
    $title = $meta['title'] ?? 'Yammbo Tv';
    $year = $meta['year'] ?? null;
    $fullTitle = $meta ? $title.($year ? " ({$year})" : '') : 'Yammbo Tv';
    $desc = $meta['description'] ?? ($isEs
        ? 'Peliculas, series, anime y TV en vivo en Yammbo Tv.'
        : 'Movies, series, anime and live TV on Yammbo Tv.');
    $ogDesc = \Illuminate\Support\Str::limit($desc, 200);
    $poster = $meta['image'] ?? null;
    $backdrop = $meta['backdrop'] ?? null;
    $fallbackImg = url(file_exists(public_path('images/og-install.jpg')) ? '/images/og-install.jpg'
        : (file_exists(public_path('images/og-install.png')) ? '/images/og-install.png' : '/images/yambo-icon.png'));
    $ogImage = $poster ?: ($backdrop ?: $fallbackImg);
    $kind = $type === 'series' ? ($isEs ? 'Serie' : 'Series') : ($type === 'movie' ? ($isEs ? 'Pelicula' : 'Movie') : null);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<meta name="color-scheme" content="dark">
<title>{{ $fullTitle }} — Yammbo Tv</title>
<meta name="description" content="{{ $ogDesc }}">
<link rel="canonical" href="{{ $shareUrl }}">
<meta property="og:type" content="{{ $type === 'series' ? 'video.tv_show' : 'video.movie' }}">
<meta property="og:site_name" content="Yammbo Tv">
<meta property="og:locale" content="{{ $isEs ? 'es_ES' : 'en_US' }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:url" content="{{ $shareUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
@if($poster)
<meta property="og:image:width" content="500">
<meta property="og:image:height" content="750">
@endif
<meta property="og:image:alt" content="{{ $fullTitle }}">
<meta name="twitter:card" content="{{ $backdrop ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<meta name="twitter:image" content="{{ $backdrop ?: $ogImage }}">
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;background:#000;color:#fff;min-height:100vh;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
  .bg{position:fixed;inset:0;z-index:0;background:#000 center/cover no-repeat;opacity:.35;filter:blur(2px)}
  .bg::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.35) 0%,#000 85%)}
  .wrap{position:relative;z-index:1;max-width:980px;margin:0 auto;padding:clamp(20px,4vw,48px) clamp(16px,4vw,32px) 64px}
  .brand{display:inline-flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font-weight:800;font-size:20px;letter-spacing:-.02em;margin-bottom:clamp(24px,5vw,56px)}
  .brand img{width:36px;height:36px}
  .card{display:grid;grid-template-columns:1fr;gap:24px;align-items:start}
  .poster{width:min(240px,62vw);aspect-ratio:2/3;border-radius:14px;object-fit:cover;background:#141416;
    box-shadow:0 20px 60px -20px rgba(0,0,0,.9);justify-self:center}
  .kind{display:inline-block;background:#E50914;color:#fff;font-size:11px;font-weight:800;letter-spacing:.09em;
    text-transform:uppercase;padding:5px 10px;border-radius:999px;margin-bottom:12px}
  h1{margin:0 0 6px;font-size:clamp(28px,5vw,44px);line-height:1.08;letter-spacing:-.03em;font-weight:800}
  .year{color:#b8b8bd;font-size:15px;margin:0 0 16px}
  .desc{color:#d0d0d6;font-size:clamp(15px,2vw,17px);line-height:1.6;margin:0 0 26px;max-width:62ch}
  .ctas{display:flex;flex-wrap:wrap;gap:10px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:48px;padding:0 22px;border-radius:12px;
    font-weight:700;font-size:15px;text-decoration:none;color:#fff;background:#E50914}
  .btn:hover{background:#c8070f}
  .btn.ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);font-weight:600}
  .btn.ghost:hover{background:rgba(255,255,255,.14)}
  .btn:focus-visible{outline:2px solid #fff;outline-offset:2px}
  .btn svg{width:18px;height:18px;fill:currentColor}
  .note{color:#8b8b93;font-size:13px;margin-top:16px}
  .note a{color:#d4d4d9}
  @media (min-width:720px){
    .card{grid-template-columns:240px 1fr;gap:40px;align-items:center}
    .poster{width:240px;justify-self:start}
  }
  @media (max-width:480px){
    .ctas .btn{flex:1 1 100%}
    .card{text-align:center}
    .desc{margin-left:auto;margin-right:auto}
    .ctas{justify-content:center}
  }
</style>
</head>
<body>
@if($backdrop)<div class="bg" style="background-image:url('{{ $backdrop }}')"></div>@endif
<div class="wrap">
  <a class="brand" href="/"><img src="/images/yambo-icon.png" alt="" width="36" height="36"><span>Yammbo Tv</span></a>

  <div class="card">
    @if($poster)
      <img class="poster" src="{{ $poster }}" alt="{{ $title }}" width="240" height="360">
    @endif
    <div>
      @if($kind)<span class="kind">{{ $kind }}</span>@endif
      <h1>{{ $title }}</h1>
      @if($year)<p class="year">{{ $year }}</p>@endif
      <p class="desc">{{ $desc }}</p>
      <div class="ctas">
        <a class="btn" href="/auth/login">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
          {{ $isEs ? 'Ver en Yammbo Tv' : 'Watch on Yammbo Tv' }}
        </a>
        <a class="btn ghost" href="/auth/register">{{ $isEs ? 'Crear cuenta gratis' : 'Create a free account' }}</a>
      </div>
      <p class="note">
        {{ $isEs ? 'Tambien en tu TV, movil o PC:' : 'Also on your TV, phone or PC:' }}
        <a href="/install">{{ $isEs ? 'descarga la app' : 'get the app' }}</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>

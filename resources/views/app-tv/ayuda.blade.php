<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<title>{{ __('app-tv.ayuda.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:var(--font);-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:760px;margin:0 auto;padding:32px 20px 80px}
  .logo{display:block;width:80px;height:80px;margin:8px auto 20px}
  h1{font-size:26px;font-weight:800;text-align:center;margin:0 0 8px;letter-spacing:-.03em}
  .subtitle{text-align:center;color:var(--muted);margin:0 0 28px;font-size:15px}
  .faq{background:var(--soft);border:1px solid var(--line);border-radius:12px;padding:20px 22px;margin-bottom:14px}
  .faq h3{margin:0 0 8px;font-size:16px;font-weight:700;color:#fff}
  .faq p{margin:0;color:var(--fg);font-size:14px;line-height:1.6}
  .faq p a{color:var(--accent-text);text-decoration:none}
  .faq p a:hover{text-decoration:underline}
  .contact{background:var(--soft);border:1px solid var(--accent);border-radius:12px;padding:20px 22px;margin-top:24px;text-align:center}
  .contact h2{margin:0 0 8px;font-size:18px;font-weight:700;color:#fff}
  .contact a{color:var(--accent-text);text-decoration:none;font-weight:600}
  .contact a:hover{text-decoration:underline}
  .back{display:block;text-align:center;color:var(--muted);text-decoration:none;font-size:13px;margin-top:28px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>{{ __('app-tv.ayuda.page_title') }}</h1>
  <p class="subtitle">{{ __('app-tv.ayuda.subtitle') }}</p>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q1_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q1_body') !!}</p>
  </div>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q2_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q2_body', ['email' => '<a href="mailto:support@yammbo.com">support@yammbo.com</a>']) !!}</p>
  </div>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q3_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q3_body') !!}</p>
  </div>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q4_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q4_body') !!}</p>
  </div>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q5_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q5_body', ['vpn_url' => '/app-tv/vpn-info']) !!}</p>
  </div>

  <div class="faq">
    <h3>{{ __('app-tv.ayuda.q6_title') }}</h3>
    <p>{!! __('app-tv.ayuda.q6_body') !!}</p>
  </div>

  <div class="contact">
    <h2>{{ __('app-tv.ayuda.contact_title') }}</h2>
    <p style="margin:0;color:var(--fg);font-size:14px">{!! __('app-tv.ayuda.contact_body', ['email' => '<a href="mailto:support@yammbo.com">support@yammbo.com</a>']) !!}</p>
  </div>

  <a href="javascript:history.back()" class="back">{{ __('app-tv.ayuda.back') }}</a>
</div>
</body>
</html>

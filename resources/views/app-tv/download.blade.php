<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<title>{{ __('app-tv.download.page_title') }}</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:560px;margin:0 auto;padding:32px 20px 80px;text-align:center}
  .logo{display:block;width:120px;height:120px;margin:8px auto 20px}
  h1{font-size:32px;font-weight:800;margin:0 0 8px;letter-spacing:-.02em}
  .subtitle{color:#bdbdbd;margin:0 0 32px;font-size:16px;line-height:1.55}
  .download-btn{display:inline-flex;align-items:center;gap:12px;background:#E50914;color:#fff;border:0;border-radius:12px;padding:18px 32px;font-size:17px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s,transform .15s;margin-bottom:24px}
  .download-btn:hover{background:#B0070F;transform:translateY(-1px)}
  .download-btn svg{width:24px;height:24px}
  .meta{color:#888;font-size:13px;margin-bottom:32px}
  .qr-card{background:#fff;border-radius:12px;padding:20px;margin:24px auto;display:inline-block}
  .qr-card img{display:block}
  .qr-caption{color:#bdbdbd;font-size:13px;margin-top:10px}
  .steps{background:#111;border:1px solid #2A2A2A;border-radius:12px;padding:22px 24px;margin:24px 0;text-align:left}
  .steps h2{margin:0 0 12px;font-size:17px;font-weight:700;text-align:center}
  .steps ol{margin:0;padding-left:22px;color:#ddd;font-size:14px;line-height:1.7}
  .steps ol li{margin-bottom:8px}
  .steps strong{color:#fff}
  .help{color:#888;font-size:13px;margin-top:20px}
  .help a{color:#E50914;text-decoration:none}
  .help a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>Yammbo Tv</h1>
  <p class="subtitle">{!! __('app-tv.download.tagline') !!}</p>

  <a class="download-btn" href="{{ $apkUrl }}" download>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    {{ __('app-tv.download.download_button') }}
  </a>
  <p class="meta">{{ __('app-tv.download.meta') }}</p>

  <div class="qr-card">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=0&data={{ urlencode($apkUrl) }}" width="200" height="200" alt="QR">
  </div>
  <p class="qr-caption">{{ __('app-tv.download.qr_caption') }}</p>

  <div class="steps">
    <h2>{{ __('app-tv.download.install_title') }}</h2>
    <ol>
      <li>{!! __('app-tv.download.install_step1') !!}</li>
      <li>{{ __('app-tv.download.install_step2') }}</li>
      <li>{!! __('app-tv.download.install_step3') !!}</li>
      <li>{{ __('app-tv.download.install_step4') }}</li>
      <li>{{ __('app-tv.download.install_step5') }}</li>
    </ol>
  </div>

  <p class="help">{!! __('app-tv.download.help', ['email' => '<a href="mailto:support@yammbo.com">support@yammbo.com</a>', 'help_url' => '/app-tv/ayuda']) !!}</p>
</div>
</body>
</html>

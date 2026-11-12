<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<title>{{ __('app-tv.vpn.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:var(--font);-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:680px;margin:0 auto;padding:32px 20px 80px}
  .icon-wrap{display:flex;justify-content:center;margin-bottom:18px}
  .icon-wrap svg{width:64px;height:64px;color:var(--accent-text)}
  h1{font-size:26px;font-weight:800;text-align:center;margin:0 0 8px;letter-spacing:-.03em}
  .subtitle{text-align:center;color:var(--muted);margin:0 0 28px;font-size:15px;line-height:1.55}
  .card{background:var(--soft);border:1px solid var(--line);border-radius:12px;padding:22px 24px;margin-bottom:16px}
  .card h2{margin:0 0 12px;font-size:18px;font-weight:700}
  .card p{margin:0 0 10px;color:var(--fg);font-size:14px;line-height:1.6}
  .card ol{margin:0;padding-left:22px;color:var(--fg);font-size:14px;line-height:1.7}
  .card ol li{margin-bottom:6px}
  .vpn-list{display:grid;gap:10px;grid-template-columns:1fr;margin-top:14px}
  @media (min-width:520px){.vpn-list{grid-template-columns:1fr 1fr}}
  .vpn{background:var(--soft-2);border:1px solid var(--line-strong);border-radius:10px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center}
  .vpn-name{font-weight:600;font-size:14px}
  .vpn-tag{background:var(--accent);color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;}
  .vpn-tag.free{background:var(--line);color:var(--muted)}
  .note{background:var(--soft);border:1px solid var(--accent);border-radius:10px;padding:14px 16px;margin-top:16px;color:var(--accent-text);font-size:13px;line-height:1.55}
  .cta-card{background:var(--soft);border:1px solid var(--accent);border-radius:var(--radius);padding:22px 24px;margin-bottom:18px;text-align:center;}
  .cta-card h2{margin:0 0 8px;font-size:19px;font-weight:800;color:#fff;letter-spacing:-.01em}
  .cta-card p{margin:0 0 16px;color:var(--fg);font-size:14px;line-height:1.55}
  .cta-btn{display:inline-block;background:var(--accent);color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:13px 28px;border-radius:10px;transition:transform .15s ease,background .15s ease}
  .cta-btn:hover,.cta-btn:active{background:var(--accent-hover);transform:translateY(-1px)}
  .cta-btn svg{vertical-align:-3px;margin-right:8px}
  .cta-hint{margin:14px 0 0;color:var(--muted);font-size:12.5px;line-height:1.5}
  .back{display:block;text-align:center;color:var(--muted);text-decoration:none;font-size:13px;margin-top:28px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">
  <div class="icon-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
  </div>
  <h1>{{ __('app-tv.vpn.heading') }}</h1>
  <p class="subtitle">{{ __('app-tv.vpn.subtitle') }}</p>

  <div class="cta-card">
    <h2>{{ __('app-tv.vpn.download_title') }}</h2>
    <p>{{ __('app-tv.vpn.download_body') }}</p>
    <a href="/download/VPN-Yammbo.apk" class="cta-btn" download>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>{{ __('app-tv.vpn.download_button') }}
    </a>
    <p class="cta-hint">{{ __('app-tv.vpn.download_hint') }}</p>
  </div>

  <div class="card">
    <h2>{{ __('app-tv.vpn.what_title') }}</h2>
    <p>{{ __('app-tv.vpn.what_body') }}</p>
  </div>

  <div class="card">
    <h2>{{ __('app-tv.vpn.how_title') }}</h2>
    <ol>
      <li>{{ __('app-tv.vpn.how_step1') }}</li>
      <li>{!! __('app-tv.vpn.how_step2') !!}</li>
      <li>{{ __('app-tv.vpn.how_step3') }}</li>
      <li>{{ __('app-tv.vpn.how_step4') }}</li>
      <li>{{ __('app-tv.vpn.how_step5') }}</li>
    </ol>
  </div>

  <div class="card">
    <h2>{{ __('app-tv.vpn.recommended_title') }}</h2>
    <div class="vpn-list">
      <div class="vpn"><span class="vpn-name">ProtonVPN</span><span class="vpn-tag free">{{ __('app-tv.vpn.tag_free') }}</span></div>
      <div class="vpn"><span class="vpn-name">Windscribe</span><span class="vpn-tag free">{{ __('app-tv.vpn.tag_free') }}</span></div>
      <div class="vpn"><span class="vpn-name">NordVPN</span><span class="vpn-tag">{{ __('app-tv.vpn.tag_premium') }}</span></div>
      <div class="vpn"><span class="vpn-name">ExpressVPN</span><span class="vpn-tag">{{ __('app-tv.vpn.tag_premium') }}</span></div>
    </div>
    <div class="note">{{ __('app-tv.vpn.note') }}</div>
  </div>

  <a href="javascript:history.back()" class="back">{{ __('app-tv.vpn.back') }}</a>
</div>
</body>
</html>

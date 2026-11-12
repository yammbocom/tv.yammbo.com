<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('app-tv.forgot_password.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:var(--font);min-height:100vh;display:flex;align-items:center;justify-content:center}
  .card{max-width:420px;width:100%;margin:20px;background:var(--soft);border:1px solid var(--line);border-radius:12px;padding:32px 26px;color:#fff}
  .logo{display:block;width:96px;height:96px;margin:0 auto 22px}
  h1{text-align:center;margin:0 0 8px;font-size:22px;font-weight:800;color:#fff}
  p.sub{text-align:center;color:var(--muted);font-size:14px;margin:0 0 22px;line-height:1.5}
  label{display:block;color:var(--fg);font-size:13px;font-weight:600;margin-bottom:6px}
  input[type="email"]{width:100%;background:var(--soft-2);border:1px solid var(--line-strong);color:#fff;padding:12px 14px;border-radius:8px;font-size:15px;outline:none;-webkit-appearance:none}
  input[type="email"]::placeholder{color:var(--muted)}
  input[type="email"]:focus{border-color:var(--accent)}
  .btn{width:100%;background:var(--accent);color:#fff;border:0;border-radius:8px;padding:12px 16px;font-size:15px;font-weight:700;cursor:pointer;margin-top:16px}
  .btn:hover{background:var(--accent-hover)}
  .msg{margin-top:14px;padding:12px 14px;border-radius:8px;font-size:14px;text-align:center}
  .msg.ok{background:var(--soft);border:1px solid var(--line-strong);color:var(--fg)}
  .msg.err{background:var(--soft);border:1px solid var(--accent);color:var(--accent-text)}
  .back{display:block;text-align:center;color:var(--muted);text-decoration:none;font-size:13px;margin-top:18px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<form method="POST" action="/forgot-password" class="card">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>{{ __('app-tv.forgot_password.heading') }}</h1>
  <p class="sub">{{ __('app-tv.forgot_password.body') }}</p>

  <label for="email">{{ __('app-tv.forgot_password.email_label') }}</label>
  <input type="email" id="email" name="email" required autocomplete="email" value="{{ $email ?? '' }}" placeholder="{{ __('app-tv.forgot_password.email_placeholder') }}">

  <button type="submit" class="btn">{{ __('app-tv.forgot_password.submit') }}</button>

  @if(!empty($message))
    <div class="msg {{ $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT ? 'ok' : 'err' }}">
      {{ $message }}
    </div>
  @endif

  <a href="javascript:history.back()" class="back">{{ __('app-tv.forgot_password.back') }}</a>
</form>
</body>
</html>

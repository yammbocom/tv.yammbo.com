<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<title>{{ __('app-tv.payment_success.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
  .card{max-width:420px;margin:20px;background:#111;border:1px solid #2A2A2A;border-radius:12px;padding:32px 26px;text-align:center;color:#fff}
  .logo{display:block;width:96px;height:96px;margin:0 auto 20px}
  .check{width:72px;height:72px;border-radius:50%;background:#E50914;display:inline-flex;align-items:center;justify-content:center;margin-bottom:18px}
  .check svg{width:38px;height:38px;stroke:#fff;stroke-width:3;fill:none;stroke-linecap:round;stroke-linejoin:round}
  h1{margin:0 0 10px;font-size:24px;font-weight:800;color:#fff}
  p{color:#bdbdbd;font-size:15px;line-height:1.55;margin:0 0 20px}
  .btn{display:inline-block;background:#E50914;color:#fff;border:0;border-radius:8px;padding:12px 22px;font-size:15px;font-weight:700;text-decoration:none;cursor:pointer}
  .btn:hover{background:#B0070F}
  .small{color:#777;font-size:12px;margin-top:14px}
</style>
</head>
<body>
<div class="card">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <span class="check" aria-hidden="true"><svg viewBox="0 0 24 24"><polyline points="5 12 10 17 19 7"></polyline></svg></span>
  <h1>{{ __('app-tv.payment_success.heading') }}</h1>
  <p>
    @if($confirmed)
      {{ __('app-tv.payment_success.confirmed') }}
    @else
      {{ __('app-tv.payment_success.pending') }}
    @endif
  </p>
  <a href="yambotvapp://continue" class="btn" id="continueBtn">{{ __('app-tv.payment_success.back_to_app') }}</a>
  <div class="small">{{ __('app-tv.payment_success.fallback_hint') }}</div>
</div>

<script>
(function(){
  setTimeout(function(){ window.location.href = 'yambotvapp://continue'; }, 1200);
})();
</script>
</body>
</html>

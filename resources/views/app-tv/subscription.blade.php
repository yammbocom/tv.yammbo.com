<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('app-tv.subscription.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:760px;margin:0 auto;padding:32px 20px 80px}
  .logo{display:block;width:96px;height:96px;margin:8px auto 20px}
  h1{font-size:26px;font-weight:800;text-align:center;margin:0 0 8px;letter-spacing:-.02em;color:#fff}
  .subtitle{text-align:center;color:#bdbdbd;margin:0 0 28px;font-size:15px}
  .toggle{display:flex;background:#111;border:1px solid #2A2A2A;border-radius:999px;padding:4px;margin:0 auto 24px;width:fit-content}
  .toggle button{flex:1;background:transparent;color:#fff;border:0;padding:10px 22px;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s}
  .toggle button.active{background:#E50914}
  .plans{display:grid;gap:16px;grid-template-columns:1fr}
  @media (min-width:640px){.plans{grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}}
  .plan{background:#111;border:1px solid #2A2A2A;border-radius:12px;padding:22px 20px;display:flex;flex-direction:column;position:relative;transition:transform .15s,border-color .15s;color:#fff}
  .plan:hover{transform:translateY(-2px);border-color:#E50914}
  .plan.recommended{border-color:#E50914;box-shadow:0 0 0 1px #E50914}
  .badge-recommended{position:absolute;top:-12px;right:16px;background:#E50914;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;letter-spacing:.04em;text-transform:uppercase}
  .plan h3{margin:0 0 8px;font-size:20px;font-weight:700;color:#fff}
  .price{font-size:32px;font-weight:800;margin:8px 0 4px;line-height:1;color:#fff}
  .price small{font-size:13px;color:#bdbdbd;font-weight:500}
  .features{list-style:none;padding:0;margin:14px 0 20px;color:#ddd;font-size:14px;line-height:1.55;flex:1}
  .features li{padding-left:18px;position:relative;margin-bottom:6px}
  .features li::before{content:"✓";color:#E50914;position:absolute;left:0;font-weight:700}
  .features li.no{color:#777}
  .features li.no::before{content:"×";color:#777}
  .btn{display:block;width:100%;background:#E50914;color:#fff;border:0;border-radius:8px;padding:13px 16px;font-size:15px;font-weight:700;cursor:pointer;text-align:center;text-decoration:none;margin-top:8px;transition:background .15s}
  .btn:hover{background:#B0070F}
  .btn.paypal{background:#1A1A1A;border:1px solid #333;margin-top:8px;color:#fff}
  .btn.paypal:hover{background:#2A2A2A}
  .err{background:#2A0E10;border:1px solid #5A1D22;color:#FFB3B8;padding:12px 14px;border-radius:8px;margin:16px 0;font-size:14px;display:none}
  .err.show{display:block}
  .back{display:block;text-align:center;color:#888;text-decoration:none;font-size:13px;margin-top:22px}
  .back:hover{color:#fff}
  .loading{display:none;text-align:center;color:#bdbdbd;font-size:14px;margin-top:16px}
  .loading.show{display:block}
</style>
</head>
<body>
<div class="wrap">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>{{ __('app-tv.subscription.heading') }}</h1>
  <p class="subtitle">
    @if($user){{ __('app-tv.subscription.hello', ['name' => $user->name ?: $user->email]) }} @endif
    {{ __('app-tv.subscription.tagline') }}
  </p>

  <div class="toggle" role="tablist">
    <button type="button" class="cycle-btn active" data-cycle="month">{{ __('app-tv.subscription.monthly') }}</button>
    <button type="button" class="cycle-btn" data-cycle="year">{{ __('app-tv.subscription.yearly') }}</button>
  </div>

  <div class="err" id="error-banner"></div>

  <div class="plans">
    @forelse($plans as $plan)
      @php
        $features = $plan->featureList();
        $monthly = $plan->monthly_price ?: '—';
        $yearly = $plan->yearly_price ?: '—';
      @endphp
      <div class="plan {{ (int) ($plan->default ?? 0) === 1 ? 'recommended' : '' }}"
           data-plan-id="{{ $plan->id }}"
           data-monthly-price-id="{{ $plan->monthly_price_id }}"
           data-yearly-price-id="{{ $plan->yearly_price_id }}">
        @if((int) ($plan->default ?? 0) === 1)
          <span class="badge-recommended">{{ __('app-tv.subscription.recommended') }}</span>
        @endif
        <h3>{{ $plan->name }}</h3>
        <div class="price month-price">{{ $plan->currency }}{{ $monthly }}<small> {{ __('app-tv.subscription.per_month') }}</small></div>
        <div class="price year-price" style="display:none">{{ $plan->currency }}{{ $yearly }}<small> {{ __('app-tv.subscription.per_year') }}</small></div>
        <ul class="features">
          @foreach($features as $f)
            <li class="{{ $f['excluded'] ? 'no' : '' }}">{{ $f['label'] }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn checkout-btn" data-gateway="stripe">{{ __('app-tv.subscription.pay_card') }}</button>
      </div>
    @empty
      <div class="plan">
        <h3>{{ __('app-tv.subscription.no_plans_title') }}</h3>
        <p style="color:#bdbdbd;margin:8px 0 0;font-size:14px">{{ __('app-tv.subscription.no_plans_body') }}</p>
      </div>
    @endforelse
  </div>

  <div class="loading" id="loading">{{ __('app-tv.subscription.redirecting') }}</div>

  <a href="javascript:history.back()" class="back">{{ __('app-tv.subscription.back') }}</a>
</div>

<script>
(function(){
  var userId = {{ (int) $user_id }};
  var deviceId = @json($device_id);
  var csrf = document.querySelector('meta[name="csrf-token"]').content;
  var cycle = 'month';
  var errEl = document.getElementById('error-banner');
  var loadingEl = document.getElementById('loading');
  var STRINGS = {
    errUnavailableCycle: @json(__('app-tv.subscription.error_unavailable_cycle')),
    errNoSession: @json(__('app-tv.subscription.error_no_session')),
    errCheckoutFailed: @json(__('app-tv.subscription.error_checkout_failed')),
    monthly: @json(__('app-tv.subscription.monthly')),
    yearly: @json(__('app-tv.subscription.yearly')),
  };

  function showError(msg){ errEl.textContent = msg; errEl.classList.add('show'); }
  function hideError(){ errEl.classList.remove('show'); }

  document.querySelectorAll('.cycle-btn').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('.cycle-btn').forEach(function(x){x.classList.remove('active');});
      b.classList.add('active');
      cycle = b.dataset.cycle;
      document.querySelectorAll('.month-price').forEach(function(e){e.style.display = cycle==='month'?'':'none';});
      document.querySelectorAll('.year-price').forEach(function(e){e.style.display = cycle==='year'?'':'none';});
    });
  });

  document.querySelectorAll('.checkout-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      hideError();
      var plan = btn.closest('.plan');
      var priceId = cycle === 'month' ? plan.dataset.monthlyPriceId : plan.dataset.yearlyPriceId;
      if(!priceId){ showError(STRINGS.errUnavailableCycle.replace(':cycle', cycle==='month'?STRINGS.monthly:STRINGS.yearly)); return; }
      if(!userId){ showError(STRINGS.errNoSession); return; }

      btn.disabled = true;
      loadingEl.classList.add('show');

      fetch('/api/app-tv/checkout', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          user_id: userId,
          price_id: priceId,
          gateway: btn.dataset.gateway,
          cycle: cycle,
          device_id: deviceId,
          _token: csrf
        })
      }).then(function(r){ return r.json().then(function(j){ return {ok:r.ok, status:r.status, body:j}; }); })
        .then(function(res){
          if(res.ok && res.body.url){ window.location.href = res.body.url; return; }
          throw new Error(res.body.error || ('Error ' + res.status));
        })
        .catch(function(e){
          loadingEl.classList.remove('show');
          btn.disabled = false;
          showError(e.message || STRINGS.errCheckoutFailed);
        });
    });
  });
})();
</script>
</body>
</html>

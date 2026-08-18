<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('app-tv.billing.page_title') }} — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:560px;margin:0 auto;padding:32px 20px 80px}
  .logo{display:block;width:80px;height:80px;margin:8px auto 20px}
  h1{font-size:26px;font-weight:800;text-align:center;margin:0 0 8px;letter-spacing:-.02em}
  .subtitle{text-align:center;color:#bdbdbd;margin:0 0 28px;font-size:15px}

  .card{background:#111;border:1px solid #2A2A2A;border-radius:12px;padding:22px 24px;margin-bottom:16px}
  .card h2{margin:0 0 14px;font-size:13px;font-weight:700;color:#bdbdbd;text-transform:uppercase;letter-spacing:.06em}
  .row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #1a1a1a}
  .row:last-child{border-bottom:0}
  .row .label{color:#888;font-size:14px}
  .row .value{color:#fff;font-size:14px;font-weight:600;text-align:right}
  .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
  .badge.active{background:#0f3a1c;color:#5dd87f}
  .badge.cancelled{background:#3a0f15;color:#ff8b9b}
  .badge.trialing{background:#1c2a3a;color:#7fbfff}

  .actions{display:grid;gap:12px;margin-top:8px}
  .btn{display:flex;align-items:center;justify-content:center;background:#E50914;color:#fff;border:0;border-radius:10px;padding:14px 18px;font-size:15px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s}
  .btn:hover{background:#B0070F}
  .btn.secondary{background:#1A1A1A;border:1px solid #333}
  .btn.secondary:hover{background:#222;border-color:#E50914}
  .btn.danger{background:transparent;border:1px solid #5A1D22;color:#FFB3B8;font-weight:600}
  .btn.danger:hover{background:#1A0608}

  .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px;font-size:14px;line-height:1.5}
  .alert.success{background:#0a2818;border:1px solid #1f5a35;color:#9be4af}
  .alert.warning{background:#2a2008;border:1px solid #5a4710;color:#e4cd6b}
  .alert.danger{background:#2a0e10;border:1px solid #5a1d22;color:#ffb3b8}

  .info{background:#1a0608;border:1px solid #5a1d22;border-radius:10px;padding:14px 16px;margin-top:16px;color:#ffb3b8;font-size:13px;line-height:1.55}
  .hint{text-align:center;color:#888;font-size:13px;margin:8px 0 0;line-height:1.5}
  .back{display:block;text-align:center;color:#888;text-decoration:none;font-size:13px;margin-top:24px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>{{ __('app-tv.billing.page_title') }}</h1>
  <p class="subtitle">{{ __('app-tv.billing.subtitle') }}</p>

  @if(session('message'))
    <div class="alert {{ session('message_type', 'success') }}">{{ session('message') }}</div>
  @endif

  <div class="card">
    <h2>{{ __('app-tv.billing.card_title') }}</h2>
    <div class="row">
      <span class="label">{{ __('app-tv.billing.account') }}</span>
      <span class="value">{{ $user->name ?? $user->email }}</span>
    </div>
    <div class="row">
      <span class="label">{{ __('app-tv.billing.plan') }}</span>
      <span class="value">{{ $planName }}</span>
    </div>
    <div class="row">
      <span class="label">{{ __('app-tv.billing.status') }}</span>
      <span class="value">
        @if($subscription->status === 'active')
          <span class="badge active">{{ __('app-tv.billing.status_active') }}</span>
        @elseif($subscription->status === 'trialing')
          <span class="badge trialing">{{ __('app-tv.billing.status_trialing') }}</span>
        @elseif($subscription->status === 'cancelled')
          <span class="badge cancelled">{{ __('app-tv.billing.status_cancelled') }}</span>
        @else
          <span class="badge">{{ $subscription->status }}</span>
        @endif
      </span>
    </div>
    @if($subscription->cycle)
      <div class="row">
        <span class="label">{{ __('app-tv.billing.cycle') }}</span>
        <span class="value">{{ $subscription->cycle === 'year' ? __('app-tv.billing.cycle_year') : ($subscription->cycle === 'month' ? __('app-tv.billing.cycle_month') : ucfirst($subscription->cycle)) }}</span>
      </div>
    @endif
    @if($subscription->trial_ends_at && \Carbon\Carbon::parse($subscription->trial_ends_at)->isFuture())
      <div class="row">
        <span class="label">{{ __('app-tv.billing.trial_ends') }}</span>
        <span class="value">{{ \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d/m/Y') }}</span>
      </div>
    @endif
    @if($subscription->ends_at)
      <div class="row">
        <span class="label">{{ $subscription->status === 'cancelled' ? __('app-tv.billing.access_until') : __('app-tv.billing.next_renewal') }}</span>
        <span class="value">{{ \Carbon\Carbon::parse($subscription->ends_at)->format('d/m/Y') }}</span>
      </div>
    @endif
    @if($subscription->vendor_slug)
      <div class="row">
        <span class="label">{{ __('app-tv.billing.payment_method') }}</span>
        <span class="value">{{ ucfirst($subscription->vendor_slug) }}</span>
      </div>
    @endif
  </div>

  <div class="actions">
    @if($subscription->status === 'active' || $subscription->status === 'trialing')
      @if($isStripe)
        {{-- Sub Stripe: Customer Portal con cancel/cambiar plan/método pago/invoices --}}
        <a class="btn" href="/mi-suscripcion/portal">{{ __('app-tv.billing.manage_subscription') }}</a>
        <p class="hint">{{ __('app-tv.billing.manage_subscription_hint') }}</p>
      @else
        {{-- Sub manual/admin: cancel local + cambiar via /pricing --}}
        <a class="btn secondary" href="/pricing">{{ __('app-tv.billing.change_plan') }}</a>
        <form id="cancel-form" method="POST" action="/mi-suscripcion/cancelar" style="margin:0">
          @csrf
          <button type="button" class="btn danger" onclick="confirmCancel()" style="width:100%">{{ __('app-tv.billing.cancel_subscription') }}</button>
        </form>
        <p class="hint">{{ __('app-tv.billing.manual_note') }}</p>
      @endif
    @elseif($subscription->status === 'cancelled')
      <div class="info">
        {{ __('app-tv.billing.cancelled_info', ['date' => $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('d/m/Y') : __('app-tv.billing.cancelled_until_period_end')]) }}
      </div>
      <a class="btn" href="/pricing">{{ __('app-tv.billing.reactivate') }}</a>
    @endif
  </div>

  <a href="/app" class="back">{{ __('app-tv.billing.back_to_app') }}</a>
</div>

<script>
function confirmCancel() {
  const msg = @json(__('app-tv.billing.cancel_confirm'));
  if (!confirm(msg)) return;
  document.getElementById('cancel-form').submit();
}
</script>
</body>
</html>

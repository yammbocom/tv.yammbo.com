<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#E50914">
<title>{{ __('app-tv.compartir.page_title') }}</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh}
  .wrap{max-width:560px;margin:0 auto;padding:32px 20px 80px;text-align:center}
  .logo{display:block;width:96px;height:96px;margin:8px auto 20px}
  h1{font-size:28px;font-weight:800;margin:0 0 8px;letter-spacing:-.02em}
  .subtitle{color:#bdbdbd;margin:0 0 28px;font-size:15px;line-height:1.55}
  .link-box{background:#111;border:1px solid #2A2A2A;border-radius:12px;padding:16px;margin:0 0 24px;display:flex;gap:8px;align-items:center}
  .link-box input{flex:1;background:transparent;border:0;color:#fff;font-size:14px;outline:none;text-overflow:ellipsis;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace}
  .copy-btn{background:#E50914;color:#fff;border:0;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;white-space:nowrap}
  .copy-btn:hover{background:#B0070F}
  .copy-btn.ok{background:#1a7c33}
  .share-main{display:block;width:100%;background:#E50914;color:#fff;border:0;border-radius:12px;padding:16px 24px;font-size:16px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s;margin-bottom:14px;display:flex;align-items:center;justify-content:center;gap:10px}
  .share-main:hover{background:#B0070F}
  .share-main svg{width:20px;height:20px}
  .actions{display:grid;gap:12px;grid-template-columns:1fr 1fr;margin-bottom:24px}
  .btn{display:flex;align-items:center;justify-content:center;gap:8px;background:#1A1A1A;border:1px solid #333;color:#fff;border-radius:10px;padding:14px 18px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:background .15s,border-color .15s}
  .btn:hover{background:#222;border-color:#E50914}
  .btn svg{width:18px;height:18px}
  .qr-card{background:#fff;border-radius:12px;padding:20px;margin:24px auto;display:inline-block}
  .qr-card img{display:block}
  .qr-caption{color:#bdbdbd;font-size:13px;margin-top:8px}
  .back{display:block;text-align:center;color:#888;text-decoration:none;font-size:13px;margin-top:28px}
  .back:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">
  <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="logo">
  <h1>{{ __('app-tv.compartir.page_title') }}</h1>
  <p class="subtitle">{{ __('app-tv.compartir.subtitle') }}</p>

  <div class="link-box">
    <input id="share-url" type="text" readonly value="https://tv.yammbo.com/app-tv/download">
    <button class="copy-btn" id="copy-btn" onclick="copyLink()">{{ __('app-tv.compartir.copy') }}</button>
  </div>

  {{-- Web Share API: en móviles modernos abre el sheet nativo del SO (WhatsApp, Mensajes, Email, etc.) --}}
  <button id="share-main-btn" class="share-main" type="button" style="display:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    {{ __('app-tv.compartir.share_button') }}
  </button>

  <div id="fallback-actions" class="actions">
    <button id="whatsapp-btn" type="button" class="btn">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
      {{ __('app-tv.compartir.whatsapp') }}
    </button>
    <button id="email-btn" type="button" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      {{ __('app-tv.compartir.email') }}
    </button>
  </div>

  <p class="subtitle" style="margin:24px 0 8px;font-size:14px">{{ __('app-tv.compartir.qr_caption') }}</p>
  <div class="qr-card">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=0&data=https%3A%2F%2Ftv.yammbo.com%2Fapp-tv%2Fdownload" width="200" height="200" alt="QR Yammbo Tv">
  </div>

  <a href="javascript:history.back()" class="back">{{ __('app-tv.compartir.back') }}</a>
</div>

<script>
const SHARE_URL = 'https://tv.yammbo.com/app-tv/download';
const SHARE_TEXT = @json(__('app-tv.compartir.share_text')) + SHARE_URL;
const EMAIL_SUBJECT = @json(__('app-tv.compartir.email_subject'));

// 1. Web Share API — se muestra solo si el navegador/WebView lo soporta (ideal: móvil)
const shareMain = document.getElementById('share-main-btn');
if (navigator.share) {
  shareMain.style.display = 'flex';
  shareMain.onclick = async () => {
    try {
      await navigator.share({ title: 'Yammbo Tv', text: SHARE_TEXT, url: SHARE_URL });
    } catch (e) {
      // user cancelled o el sheet falló — silently
    }
  };
}

// 2. Botones fallback — funcionan también en navegadores sin Web Share (desktop)
//    Importante: en WebView de la APK, los hrefs wa.me/mailto pueden no abrir apps externas;
//    usamos window.location.href que dispara intent del sistema más confiablemente.
document.getElementById('whatsapp-btn').onclick = () => {
  window.location.href = 'https://wa.me/?text=' + encodeURIComponent(SHARE_TEXT);
};
document.getElementById('email-btn').onclick = () => {
  window.location.href = 'mailto:?subject=' + encodeURIComponent(EMAIL_SUBJECT) + '&body=' + encodeURIComponent(SHARE_TEXT);
};

function copyLink(){
  const input = document.getElementById('share-url');
  const btn = document.getElementById('copy-btn');
  input.select();
  input.setSelectionRange(0, 99999);
  try{
    if(navigator.clipboard){ navigator.clipboard.writeText(input.value); }
    else{ document.execCommand('copy'); }
    btn.textContent = @json(__('app-tv.compartir.copied'));
    btn.classList.add('ok');
    setTimeout(()=>{btn.textContent=@json(__('app-tv.compartir.copy'));btn.classList.remove('ok')}, 2000);
  }catch(e){}
}
</script>
</body>
</html>

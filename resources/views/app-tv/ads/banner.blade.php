<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<title>Yammbo Tv Premium</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
<link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{margin:0;padding:0;background:#000;color:#fff;font-family:var(--font);min-height:100vh}
  .wrap{max-width:560px;margin:0 auto;padding:32px 22px 60px;text-align:center}
  .hero{background:var(--accent);border-radius:var(--radius);padding:36px 24px;margin-bottom:24px}
  .hero h1{font-size:30px;margin:0 0 6px;font-weight:800;letter-spacing:-.03em}
  .hero .lead{color:var(--fg);margin:0;font-size:15px}
  .features{display:flex;flex-direction:column;gap:14px;margin:24px 0}
  .f{display:flex;align-items:center;gap:14px;background:var(--soft);border:1px solid var(--line);border-radius:12px;padding:16px 18px;text-align:left}
  .f .ico{flex-shrink:0;width:40px;height:40px;border-radius:50%;background:var(--soft);border:1px solid var(--line-strong);display:flex;align-items:center;justify-content:center;color:var(--accent-text);font-size:18px;font-weight:800}
  .f .t{flex:1}
  .f .t b{display:block;font-size:15px;margin:0 0 2px}
  .f .t span{color:var(--muted);font-size:13px}
  .cta{display:block;width:100%;background:var(--accent);color:#fff;text-decoration:none;padding:16px 22px;border-radius:30px;font-weight:700;font-size:16px;margin-top:6px;transition:transform .15s}
  .cta:active{transform:scale(.98)}
  .note{color:var(--muted);font-size:12px;margin-top:14px;line-height:1.5}
</style>
</head>
<body>
  <div class="wrap">
    <div class="hero">
      <h1>Yammbo Tv Premium</h1>
      <p class="lead">Mira sin l&iacute;mites. Sin interrupciones.</p>
    </div>

    <div class="features">
      <div class="f">
        <span class="ico">✓</span>
        <div class="t">
          <b>Sin anuncios</b>
          <span>Mira tus pel&iacute;culas y series sin pausas</span>
        </div>
      </div>
      <div class="f">
        <span class="ico">✓</span>
        <div class="t">
          <b>Sin interrupciones</b>
          <span>Reproducci&oacute;n continua, calidad HD</span>
        </div>
      </div>
      <div class="f">
        <span class="ico">✓</span>
        <div class="t">
          <b>Soporte prioritario</b>
          <span>Respuesta r&aacute;pida cuando necesites ayuda</span>
        </div>
      </div>
    </div>

    <a class="cta" href="https://tv.yammbo.com/app-tv/subscription">Probar gratis 7 d&iacute;as</a>
    <p class="note">Sin compromiso. Cancela cuando quieras.</p>
  </div>
</body>
</html>

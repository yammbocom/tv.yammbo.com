<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Enlace para compartir una pelicula o serie desde el movil: /ver/{contentId}?p=movie&n=Titulo
 *
 * Con la app instalada, Android abre la app directamente (App Link verificado con
 * /.well-known/assetlinks.json). Si no, se ve esta pagina: "Abrir en la app" (intent://, por si
 * el enlace no se verifico) y "Descargar Yammbo Tv". Rutas relativas: se sirve por el host neutro.
 */
class ShareLinkController extends Controller
{
    private const PKG = 'com.msandroid.mobileNF';
    private const HOST = 'edge-x7q2m9.pages.dev';

    public function __invoke(Request $r, string $id)
    {
        $title = mb_substr(trim((string) $r->query('n', '')), 0, 120);
        $type = $r->query('p') === 'movie' ? 'movie' : ($r->query('p') ? 'serie' : '');
        $host = self::HOST;
        $path = '/ver/' . rawurlencode($id) . '?' . http_build_query(array_filter([
            'p' => $r->query('p'),
            't' => $r->query('t'),
            'n' => $title,
        ]));
        $fallback = 'https://' . $host . '/download/movil';
        $intent = 'intent://' . $host . $path . '#Intent;scheme=https;package=' . self::PKG
            . ';S.browser_fallback_url=' . rawurlencode($fallback) . ';end';

        $t = e($title !== '' ? $title : 'Yammbo Tv');
        $kind = $type === 'movie' ? 'esta película' : ($type === 'serie' ? 'esta serie' : 'este título');
        $intentAttr = e($intent);

        $html = <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="dark">
<title>{$t} en Yammbo Tv</title>
<meta property="og:title" content="Mira {$t} en Yammbo Tv">
<meta property="og:description" content="Películas, series, TV en vivo y podcasts.">
<style>
:root{color-scheme:dark}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
background:radial-gradient(circle at 20% 0%,#3a0a0e 0,#0b0b0e 55%);color:#fff;
font-family:system-ui,-apple-system,Roboto,sans-serif;padding:24px}
main{width:100%;max-width:420px;text-align:center}
h1{font-size:26px;margin:0 0 8px}
p{color:#a7a7b3;margin:0 0 28px;line-height:1.5}
a{display:block;border-radius:14px;padding:16px;font-weight:700;text-decoration:none;font-size:16px}
.open{background:#fff;color:#0b0b0e;margin-bottom:12px}
.get{border:1px solid rgba(255,255,255,.3);color:#fff}
small{display:block;color:#6f6f7a;margin-top:22px}
</style>
</head>
<body>
<main>
<h1>{$t}</h1>
<p>Ábrelo en Yammbo Tv para ver {$kind}.</p>
<a class="open" href="{$intentAttr}">Abrir en la app</a>
<a class="get" href="/download/movil">Descargar Yammbo Tv</a>
<small>¿No se abre? Instala la app y vuelve a tocar el enlace.</small>
</main>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300');
    }
}

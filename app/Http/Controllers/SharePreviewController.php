<?php

namespace App\Http\Controllers;

use App\Services\SharePreview;
use Illuminate\Http\Request;

/**
 * Enlace para compartir una ficha: /app/detail/{type}/{id}/{resto}.
 *
 * La app usa HashRouter (/app/#/detail/...) y lo que va tras "#" nunca llega al
 * servidor, así que los bots de WhatsApp/Telegram/Facebook solo veían /app (y el
 * login). Esta ruta sin "#" devuelve las etiquetas OG de la ficha.
 *  - Con sesión: redirige directo a la ficha dentro de la app.
 *  - Sin sesión (y los bots): página de vista previa. Se guarda esta URL como
 *    "intended" para que, tras iniciar sesión, vuelva aquí y de aquí a la ficha.
 */
class SharePreviewController extends Controller
{
    public function show(Request $request, SharePreview $previews, string $type, string $id, ?string $rest = null)
    {
        $appPath = '/app/#/detail/'.rawurlencode($type).'/'.rawurlencode($id).($rest !== null && $rest !== '' ? '/'.implode('/', array_map('rawurlencode', explode('/', $rest))) : '');

        if (auth()->check()) {
            return redirect($appPath);
        }

        $shareUrl = url('/app/detail/'.rawurlencode($type).'/'.rawurlencode($id).($rest ? '/'.$rest : ''));
        redirect()->setIntendedUrl($shareUrl);

        $meta = $previews->for($type, $id, app()->getLocale());

        return response()
            ->view('share-preview', [
                'meta' => $meta,
                'type' => $type,
                'shareUrl' => $shareUrl,
                'appPath' => $appPath,
            ])
            ->header('Cache-Control', 'no-cache, private');
    }
}

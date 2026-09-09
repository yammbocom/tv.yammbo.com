<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * QR en PNG para las pantallas NATIVAS de la app de TV (Android no decodifica
 * SVG). Se dibuja con GD a partir de la matriz de BaconQrCode.
 *
 * GET /qr.png?d=<url a codificar>&s=<lado en px>
 */
class QrPngController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = (string) $request->query('d', '');
        $size = (int) $request->query('s', 480);
        $size = max(120, min(900, $size));

        if ($data === '' || strlen($data) > 1200) {
            abort(400, 'parametro d invalido');
        }

        $matrix = Encoder::encode($data, \BaconQrCode\Common\ErrorCorrectionLevel::M())
            ->getMatrix();
        $n = $matrix->getWidth();

        $quiet = 2;                        // margen en modulos
        $total = $n + $quiet * 2;
        $scale = max(1, (int) floor($size / $total));
        $px    = $total * $scale;

        $img   = imagecreatetruecolor($px, $px);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $px, $px, $white);

        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $x0 = ($x + $quiet) * $scale;
                    $y0 = ($y + $quiet) * $scale;
                    imagefilledrectangle($img, $x0, $y0, $x0 + $scale - 1, $y0 + $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}

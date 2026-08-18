<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Mock del portal API legacy (XuperTV/Koocan) para que el APK YamboTV cargue
 * post-rebuild VPS 2026-04-20. El backend nuevo (Wave) no replica el portal
 * original, pero el APK lo llama al startup en /api/portalCore/v8/active y
 * espera ActiveResult{returnCode,errorMessage,data:UserData}.
 *
 * Estrategia: devolver respuesta exitosa mínima con campos plausibles para
 * que la app continúe el flujo normal (login real ya va a /api/app-tv/login).
 */
class PortalCoreController extends Controller
{
    /**
     * POST /api/portalCore/v8/active
     *
     * El APK envía body string (probablemente device info encriptado). No lo
     * parseamos — devolvemos siempre success.
     */
    public function active(Request $request): JsonResponse
    {
        Log::channel('daily')->info('[portalCore/active] called', [
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
            'body_size' => strlen($request->getContent()),
        ]);

        return response()->json([
            'returnCode' => '0',
            'errorMessage' => '',
            'data' => [
                'userId' => '0',
                'userToken' => '',
                'userType' => '0',
                'userIdentity' => '0',
                'verificationToken' => '',
                'restrictedStatus' => '0',
                'expRemainingDays' => 9999,
                'remainingDays' => 9999,
                'availableTime' => 9999,
                'playlistUrl' => '',
                'payCoreAddress' => 'https://tv.yammbo.com',
                'nowTime' => (string) (time() * 1000),
                'heartBeatTime' => '60',
                'cacheTime' => '300',
                'showFlag' => '1',
                'showType' => '0',
                'hasPay' => '1',
                'hasFreeAuth' => '1',
                'hasPwd' => '0',
                'getFreeAuthFlag' => '0',
                'getFreeAuthDays' => '0',
                'renewFlag' => '0',
                'invitedStatus' => '0',
                'inviteCode' => '',
                'qrcodeDisplay' => '0',
                'qrcodeMessage' => '',
                'tips' => '',
                'type' => '1',
                'customer' => '',
                'accountType' => '1',
                'activeTime' => (string) (time() * 1000),
                'appModel' => '',
                'areaCode' => 'ES',
                'areaFlag' => '1',
                'authInfoList' => [],
                'bindGoogle' => '0',
                'bindGoogleEmail' => '',
                'bindMail' => '',
                'bindMobile' => '',
                'childLockPwd' => '',
                'email' => '',
                'googleEmail' => '',
                'googleNickName' => '',
                'googlePhotoUrl' => '',
                'mobile' => '',
                'pwdTip' => '',
                'portalCodeList' => [
                    ['portalCode' => 'masmobile', 'type' => '1'],
                ],
            ],
        ]);
    }

    /**
     * Catch-all para resto de endpoints /api/portalCore/* — log + respuesta vacía
     * mínima. Permite ver qué más llama el APK sin romper nada.
     */
    public function fallback(Request $request, string $path = ''): JsonResponse
    {
        Log::channel('daily')->info('[portalCore/fallback] hit', [
            'method' => $request->method(),
            'path' => $request->path(),
            'body_size' => strlen($request->getContent()),
        ]);

        return response()->json([
            'returnCode' => '0',
            'errorMessage' => '',
            'data' => null,
        ]);
    }
}

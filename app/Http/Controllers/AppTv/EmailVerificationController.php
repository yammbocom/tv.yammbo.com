<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Verificacion de correo para cuentas nuevas (anti cuentas falsas).
 *
 * - sendLink(): manda un enlace firmado que caduca en 48h.
 * - verify(): marca la cuenta como verificada.
 * - resend(): reenvia el enlace.
 *
 * Las cuentas creadas antes de activar esto quedaron marcadas como verificadas,
 * asi que nadie que ya usaba la app se queda fuera.
 */
class EmailVerificationController extends Controller
{
    /** Genera y envia el enlace de verificacion. Devuelve true si salio el correo. */
    public static function sendLink(User $user): bool
    {
        if ($user->email_verified_at) {
            return true;
        }

        $url = URL::temporarySignedRoute(
            'app-tv.verificar-correo',
            now()->addHours(48),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Plantilla comun de Yambo TV (resources/views/emails/layout)
        return \App\Support\YamboMail::verifyEmail($user, $url);
    }

    /** Enlace del correo: marca la cuenta como verificada. */
    public function verify(Request $request, $id, $hash)
    {
        if (! $request->hasValidSignature()) {
            return response()->view('verificar-correo', [
                'ok' => false,
                'msg' => 'El enlace caduco o no es valido. Pide uno nuevo desde la app.',
            ], 403);
        }

        $user = User::find((int) $id);
        if (! $user || ! hash_equals(sha1($user->email), (string) $hash)) {
            return response()->view('verificar-correo', [
                'ok' => false,
                'msg' => 'No encontramos esa cuenta.',
            ], 404);
        }

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        return response()->view('verificar-correo', [
            'ok' => true,
            'msg' => 'Ya puedes iniciar sesion en tu TV.',
        ]);
    }

    /** Reenvia el enlace (desde la web del movil o la app). */
    public function resend(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email')));
        $user = $email !== '' ? User::where('email', $email)->first() : null;

        // Respuesta neutra: no revelamos si el correo existe
        if ($user && ! $user->email_verified_at) {
            self::sendLink($user);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return response()->view('verificar-correo', [
            'ok' => true,
            'msg' => 'Si esa cuenta existe y falta confirmarla, te enviamos el correo otra vez.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    /** Desde aqui la bienvenida sale al confirmar el correo, no al registrarse (UTC). */
    private const WELCOME_ON_VERIFY_SINCE = '2026-11-14 23:50:00';

    /**
     * Genera y envia el enlace de verificacion. Devuelve true si salio el correo
     * o si ya se mando uno hace menos de $minGapSeconds.
     *
     * El hueco minimo es por usuario y vale para todos los que llaman: el QR de
     * la TV consulta cada 3 s y, sin el, mandaba un correo en cada consulta.
     */
    public static function sendLink(User $user, int $minGapSeconds = 60): bool
    {
        if ($user->email_verified_at) {
            return true;
        }

        $key = 'tv-verify-mail:' . $user->id;
        $last = (int) Cache::get($key, 0);
        if ($last > 0 && time() - $last < $minGapSeconds) {
            return true;
        }
        // Se marca antes de enviar para que dos consultas seguidas no manden dos
        Cache::put($key, time(), now()->addDay());

        $url = URL::temporarySignedRoute(
            'app-tv.verificar-correo',
            now()->addHours(48),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Plantilla comun de Yambo TV (resources/views/emails/layout)
        $sent = \App\Support\YamboMail::verifyEmail($user, $url);
        if (! $sent) {
            Cache::forget($key);
        }

        return $sent;
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

        // Condicional: con doble clic (o el escaner de enlaces del correo + el
        // clic) solo una peticion marca la cuenta y manda la bienvenida.
        $marked = User::whereKey($user->id)->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
        // Las altas anteriores a este cambio ya recibieron la bienvenida al registrarse.
        if ($marked === 1 && $user->created_at?->gte(self::WELCOME_ON_VERIFY_SINCE)) {
            self::sendWelcome($user->fresh());
        }

        return response()->view('verificar-correo', [
            'ok' => true,
            'msg' => 'Ya puedes entrar en Yammbo Tv desde este navegador o desde tu TV.',
            'cta' => ['text' => 'Entrar a Yammbo Tv', 'url' => url('/app/')],
        ]);
    }

    /** Bienvenida tras confirmar. Sin fechas: el acceso inicial no se anuncia. */
    private static function sendWelcome(User $user): void
    {
        try {
            \App\Support\YamboMail::welcome($user);
        } catch (\Throwable $e) {
            \Log::error('verificacion, bienvenida: '.$e->getMessage());
        }
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

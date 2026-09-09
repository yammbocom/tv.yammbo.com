<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

/**
 * Pantalla para fijar una nueva contrasena desde el enlace del correo.
 *
 * GET  /auth/reset-password/{token}?email=...  -> formulario
 * POST /auth/reset-password                     -> aplica el cambio
 *
 * El token es el del broker estandar de Laravel (Password::sendResetLink lo
 * genero en User::sendPasswordResetNotification). Aqui se valida con
 * Password::reset contra la tabla password_reset_tokens.
 */
class ResetPasswordController extends Controller
{
    public function show(Request $request, string $token)
    {
        return View::make('app-tv.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'error' => null,
            'done'  => false,
        ]);
    }

    public function reset(Request $request)
    {
        $this->assertCsrf($request);

        $data = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data['email'] = strtolower($data['email']);

        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return View::make('app-tv.reset-password', [
                'token' => $data['token'],
                'email' => $data['email'],
                'error' => null,
                'done'  => true,
            ]);
        }

        $errores = [
            Password::INVALID_USER    => 'No encontramos ninguna cuenta con ese correo.',
            Password::INVALID_TOKEN   => 'El enlace caduco o no es valido. Pide uno nuevo desde "Olvide mi contrasena".',
            Password::RESET_THROTTLED => 'Espera un momento antes de intentarlo de nuevo.',
        ];

        return View::make('app-tv.reset-password', [
            'token' => $data['token'],
            'email' => $data['email'],
            'error' => $errores[$status] ?? 'No se pudo cambiar la contrasena. Intentalo de nuevo.',
            'done'  => false,
        ]);
    }

    private function assertCsrf(Request $request): void
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        if (! $token || ! hash_equals((string) $request->session()->token(), (string) $token)) {
            throw new TokenMismatchException('CSRF token mismatch.');
        }
    }
}

<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

/**
 * GET /forgot-password
 *   Blade con formulario de reset. Usado por NativeAuthAty vía WebViewAty
 *   cuando el user tap en "Olvidé mi contraseña".
 *
 * POST /forgot-password
 *   Envía el email de reset usando Laravel's default Password broker.
 */
class AppTvForgotPasswordController extends Controller
{
    public function show()
    {
        return View::make('app-tv.forgot-password', [
            'status' => null,
            'message' => null,
            'email' => '',
        ]);
    }

    public function submit(Request $request)
    {
        $this->assertCsrf($request);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $status = Password::sendResetLink(['email' => strtolower($validated['email'])]);

        $message = $status === Password::RESET_LINK_SENT
            ? __('app-tv.forgot_password.success')
            : __('app-tv.forgot_password.error');

        return View::make('app-tv.forgot-password', [
            'status' => $status,
            'message' => $message,
            'email' => $validated['email'],
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

<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * QR / TV link login para YamboTV Android TV (APK v5.4+).
 *
 * Flow:
 *   1) APK abre WebViewAty con bundleUrl=https://tv.yammbo.com/tv-link-app?source=tv
 *   2) /tv-link-app llama POST /api/app-tv/tv-link/generate -> { code, confirm_url }
 *   3) Página muestra QR (encodea confirm_url) + el código de 8 chars
 *   4) Usuario escanea QR con su phone/otro device -> /tv-link?code=X -> form login
 *   5) POST /tv-link/confirm valida credenciales -> marca code linked + guarda user_id
 *   6) /tv-link-app polls POST /api/app-tv/tv-link/poll cada 3s
 *   7) Cuando status=linked, redirige a yambotvapp://authorized?name=X&email=Y&user_id=Z&subscription_active=1
 *      (subscription_active solo se incluye si el user tiene sub activa — APK lo lee y lo guarda en SharedPrefs)
 *   8) WebViewAty$j captura el deep link -> guarda en SharedPrefs yambo_prefs -> finish()
 */
class TvLinkController extends Controller
{
    private const CODE_TTL_MINUTES = 15;

    /**
     * Margen para que la TV recoja la sesión después de que el móvil confirme.
     * Pasado esto el código muere: antes, un código enlazado se saltaba la
     * comprobación de caducidad y seguía devolviendo nombre, email y estado de
     * suscripción indefinidamente.
     */
    private const LINKED_GRACE_MINUTES = 5;

    public function generate(): JsonResponse
    {
        do {
            $code = strtoupper(Str::random(8));
            $exists = DB::table('tv_link_codes')->where('code', $code)->exists();
        } while ($exists);

        $now = Carbon::now();
        DB::table('tv_link_codes')->insert([
            'code' => $code,
            'expires_at' => $now->copy()->addMinutes(self::CODE_TTL_MINUTES),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $confirmUrl = url('/tv-link?code=' . $code);

        $qr = null;
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(280, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $svg = (new \BaconQrCode\Writer($renderer))->writeString($confirmUrl);
            $qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            $qr = null;
        }

        return response()->json([
            'code' => $code,
            'expires_in' => self::CODE_TTL_MINUTES * 60,
            'confirm_url' => $confirmUrl,
            'qr' => $qr,
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $code = strtoupper((string) $request->input('code'));
        if ($code === '') {
            return response()->json(['status' => 'invalid'], 400);
        }

        $row = DB::table('tv_link_codes')->where('code', $code)->first();
        if (!$row) {
            return response()->json(['status' => 'invalid'], 404);
        }

        $expired = $row->linked_at
            ? Carbon::parse($row->linked_at)->addMinutes(self::LINKED_GRACE_MINUTES)->isPast()
            : Carbon::parse($row->expires_at)->isPast();

        if ($expired) {
            return response()->json(['status' => 'expired']);
        }

        if (!$row->user_id) {
            return response()->json(['status' => 'pending']);
        }

        $user = User::find($row->user_id);
        if (!$user) {
            return response()->json(['status' => 'invalid'], 404);
        }

        // Sin correo confirmado no se entra (la TV muestra el aviso)
        if (!$user->email_verified_at) {
            EmailVerificationController::sendLink($user);
            return response()->json([
                'status' => 'unverified',
                'email'  => $user->email,
            ]);
        }

        // Registrar el aparato y aplicar el limite del plan (servidor manda)
        $limits = ['plan' => null, 'devices' => 1, 'live_tv' => true];
        try {
            $deviceId = (string) $request->input('device_id', '');
            $deviceName = (string) $request->input('device_name', '');
            if ($deviceId !== '') {
                \App\Support\DeviceGuard::register($user, $deviceId, 'tv', $deviceName);
            }
            $limits = \App\Support\DeviceGuard::limitsOf($user);
        } catch (\Throwable $e) { \Log::warning('device guard: '.$e->getMessage()); }

        // Magic-link de larga duracion (30 dias) para "Administrar suscripcion" desde la TV.
        $manageToken = null;
        try {
            \Tymon\JWTAuth\Facades\JWTAuth::factory()->setTTL(43200);
            $manageToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        } catch (\Throwable $e) {
            $manageToken = null;
        }

        return response()->json([
            'status' => 'linked',
            'subscription_active' => AppTvAuthController::isSubscriptionActive($user),
            'limits' => $limits,
            'manage_token' => $manageToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function show(Request $request): View
    {
        $code = strtoupper((string) $request->query('code', ''));
        $status = 'missing';
        if ($code !== '') {
            $row = DB::table('tv_link_codes')->where('code', $code)->first();
            if (!$row) {
                $status = 'invalid';
            } elseif (Carbon::parse($row->expires_at)->isPast()) {
                $status = 'expired';
            } elseif ($row->linked_at) {
                $status = 'already_linked';
            } else {
                $status = 'ready';
            }
        }
        return view('tv-link.confirm', [
            'code' => $code,
            'status' => $status,
            'error' => null,
        ]);
    }

    public function confirm(Request $request)
    {
        $code = strtoupper((string) $request->input('code'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        $row = DB::table('tv_link_codes')->where('code', $code)->first();
        if (!$row) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'invalid', 'error' => 'Codigo invalido.']);
        }
        if (Carbon::parse($row->expires_at)->isPast()) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'expired', 'error' => 'El codigo expiro.']);
        }
        if ($row->linked_at) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'already_linked', 'error' => null]);
        }

        $user = User::where('email', $email)->whereNotNull('password')->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return view('tv-link.confirm', [
                'code' => $code,
                'status' => 'ready',
                'error' => 'Email o contrasena incorrectos.',
            ]);
        }

        DB::table('tv_link_codes')->where('code', $code)->update([
            'user_id' => $user->id,
            'linked_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return view('tv-link.confirm', ['code' => $code, 'status' => 'success', 'error' => null]);
    }

    /**
     * Registro de cuenta NUEVA desde la pagina del QR (usuario sin cuenta).
     * Pide nombre, correo y contrasena, crea la cuenta (con prueba de 7 dias),
     * vincula la TV y dispara el correo de verificacion (via User::created).
     */
    public function register(Request $request)
    {
        $code = strtoupper((string) $request->input('code'));
        $name = trim((string) $request->input('name'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        $row = DB::table('tv_link_codes')->where('code', $code)->first();
        if (!$row) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'invalid', 'error' => 'Codigo invalido.', 'mode' => 'register']);
        }
        if (Carbon::parse($row->expires_at)->isPast()) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'expired', 'error' => 'El codigo expiro.', 'mode' => 'register']);
        }
        if ($row->linked_at) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'already_linked', 'error' => null, 'mode' => 'register']);
        }

        // Validacion basica
        $err = null;
        if ($name === '' || mb_strlen($name) < 2) {
            $err = 'Escribe tu nombre.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'El correo no es valido.';
        } elseif (mb_strlen($password) < 6) {
            $err = 'La contrasena debe tener al menos 6 caracteres.';
        }
        if ($err) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'ready', 'error' => $err, 'mode' => 'register']);
        }

        // Ya existe: mandar a iniciar sesion
        if (User::where('email', $email)->exists()) {
            return view('tv-link.confirm', [
                'code' => $code, 'status' => 'ready', 'mode' => 'login',
                'error' => 'Ya existe una cuenta con ese correo. Inicia sesion.',
            ]);
        }

        // Crear la cuenta (User::created envia verificacion + bienvenida) con prueba de 7 dias
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'trial_ends_at' => Carbon::now()->addDays(7),
            ]);
        } catch (\Throwable $e) {
            return view('tv-link.confirm', ['code' => $code, 'status' => 'ready', 'error' => 'No se pudo crear la cuenta, intentalo de nuevo.', 'mode' => 'register']);
        }

        // Vincular la TV a la cuenta nueva (la TV pedira verificar el correo antes de entrar)
        DB::table('tv_link_codes')->where('code', $code)->update([
            'user_id' => $user->id,
            'linked_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return view('tv-link.confirm', ['code' => $code, 'status' => 'registered', 'error' => null, 'mode' => 'register']);
    }
}

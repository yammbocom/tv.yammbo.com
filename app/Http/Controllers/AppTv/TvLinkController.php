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

        return response()->json([
            'code' => $code,
            'expires_in' => self::CODE_TTL_MINUTES * 60,
            'confirm_url' => url('/tv-link?code=' . $code),
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

        return response()->json([
            'status' => 'linked',
            'subscription_active' => AppTvAuthController::isSubscriptionActive($user),
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
}

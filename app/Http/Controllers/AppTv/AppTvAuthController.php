<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Wave\Subscription;

/**
 * Endpoints JSON para la APK YamboTV v23 (NativeAuthAty).
 *
 * Formato de request/response verificado contra smali:
 * - POST /api/app-tv/login   body: {email, password}
 * - POST /api/app-tv/register body: {first_name, last_name, email, password}
 * - Response success:
 *   {
 *     "user": { "id": <int>, "name": "First Last", "email": "..." },
 *     "subscription_active": true|false,
 *     "access_token": "<jwt>"
 *   }
 * - Response error:
 *   { "error": "mensaje en español", "field": "email"|"password"|null }
 */
class AppTvAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'field' => array_key_first($validator->errors()->toArray()),
            ], 422);
        }

        $user = User::where('email', strtolower($request->input('email')))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => __('app-tv.auth.invalid_credentials'),
                'field' => 'password',
            ], 401);
        }

        $token = JWTAuth::fromUser($user);

        return $this->successResponse($user, $token);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:255',
        ], [
            'email.unique' => __('app-tv.auth.email_taken'),
            'email.email' => __('app-tv.auth.email_invalid'),
            'password.min' => __('app-tv.auth.password_min'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'field' => array_key_first($validator->errors()->toArray()),
            ], 422);
        }

        $fullName = trim($request->input('first_name').' '.(string) $request->input('last_name'));

        $user = User::create([
            'name' => $fullName,
            'email' => strtolower($request->input('email')),
            'password' => Hash::make($request->input('password')),
            'trial_ends_at' => Carbon::now()->addDays(7),
        ]);

        Subscription::create([
            'billable_type' => 'user',
            'billable_id' => $user->id,
            'plan_id' => config('apptv.trial_plan_id', 1),
            'vendor_slug' => 'trial',
            'cycle' => 'month',
            'status' => 'trialing',
            'seats' => 1,
            'trial_ends_at' => $user->trial_ends_at,
            'ends_at' => $user->trial_ends_at,
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->successResponse($user->fresh(), $token);
    }

    private function successResponse(User $user, string $token): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'subscription_active' => $this->isSubscriptionActive($user),
            'access_token' => $token,
        ]);
    }

    public static function isSubscriptionActive(User $user): bool
    {
        $now = Carbon::now();

        $active = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->exists();

        if ($active) {
            return true;
        }

        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return true;
        }

        return false;
    }
}

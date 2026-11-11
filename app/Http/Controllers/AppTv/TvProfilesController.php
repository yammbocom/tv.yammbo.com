<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\TvProfile;
use App\Models\User;
use App\Support\AppTvToken;
use App\Support\TvAudience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Perfiles de la cuenta en la app de TV ("Quien esta viendo").
 *
 *   GET  /api/app-tv/profiles?t=          lista (la crea con el nombre de la cuenta si no hay ninguno)
 *   POST /api/app-tv/profiles/save        t, id?, name, avatar?, color?, kids?
 *   POST /api/app-tv/profiles/delete      t, id
 */
class TvProfilesController extends Controller
{
    public const MAX = 5;

    public function index(Request $request): JsonResponse
    {
        $user = TvAudience::user((string) $request->query('t', ''));
        if (! $user) {
            return response()->json(['ok' => false, 'profiles' => []], 401);
        }

        return response()->json(['ok' => true, 'max' => self::MAX, 'profiles' => $this->list($user)]);
    }

    public function save(Request $request): JsonResponse
    {
        [$user, $expired] = AppTvToken::resolve((string) $request->input('t', ''));
        if (! $user || $expired) {
            return response()->json(['ok' => false, 'error' => 'session'], 401);
        }

        $v = validator($request->all(), [
            'id' => 'nullable|integer',
            'name' => 'required|string|min:1|max:30',
            'avatar' => ['nullable', 'string', 'max:500', 'regex:#^https://image\.tmdb\.org/t/p/[\w/.\-]+$#'],
            'color' => 'nullable|integer|min:0|max:7',
            'kids' => 'nullable|boolean',
        ]);
        if ($v->fails()) {
            return response()->json(['ok' => false, 'error' => 'invalid'], 422);
        }
        $data = $v->validated();
        $name = trim(preg_replace('/\s+/u', ' ', $data['name']));
        if ($name === '') {
            return response()->json(['ok' => false, 'error' => 'invalid'], 422);
        }

        if (! empty($data['id'])) {
            $p = TvProfile::where('user_id', $user->id)->find($data['id']);
            if (! $p) {
                return response()->json(['ok' => false, 'error' => 'not_found'], 404);
            }
        } else {
            $this->list($user);   // asegura el perfil principal antes de contar
            if (TvProfile::where('user_id', $user->id)->count() >= self::MAX) {
                return response()->json(['ok' => false, 'error' => 'max'], 422);
            }
            $p = new TvProfile(['user_id' => $user->id]);
            $p->position = (int) TvProfile::where('user_id', $user->id)->max('position') + 1;
        }

        $p->name = $name;
        $p->avatar = $data['avatar'] ?? null;
        $p->color = (int) ($data['color'] ?? 0);
        $p->kids = (bool) ($data['kids'] ?? false);
        $p->save();

        return response()->json(['ok' => true, 'profile' => $p->toApp(), 'profiles' => $this->list($user)]);
    }

    public function delete(Request $request): JsonResponse
    {
        [$user, $expired] = AppTvToken::resolve((string) $request->input('t', ''));
        if (! $user || $expired) {
            return response()->json(['ok' => false, 'error' => 'session'], 401);
        }
        $p = TvProfile::where('user_id', $user->id)->find((int) $request->input('id'));
        if (! $p) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }
        // siempre queda al menos uno
        if (TvProfile::where('user_id', $user->id)->count() <= 1) {
            return response()->json(['ok' => false, 'error' => 'last'], 422);
        }
        $p->delete();

        return response()->json(['ok' => true, 'profiles' => $this->list($user)]);
    }

    private function list(User $user): array
    {
        $rows = TvProfile::where('user_id', $user->id)->orderBy('position')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            $first = trim(explode(' ', trim((string) $user->name))[0] ?? '') ?: 'Principal';
            $rows = collect([TvProfile::create([
                'user_id' => $user->id, 'name' => mb_substr($first, 0, 30), 'color' => 0, 'kids' => false, 'position' => 0,
            ])]);
        }

        return $rows->map(fn (TvProfile $p) => $p->toApp())->values()->all();
    }
}

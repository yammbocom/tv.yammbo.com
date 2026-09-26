<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

/**
 * Planes de suscripción. Absorbido de Wave\Plan para poder retirar el paquete:
 * apunta a la misma tabla `plans`, así que convive con la clase vieja mientras
 * dure la migración.
 */
class Plan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'limits' => 'array',
        'features' => 'array',
    ];

    /**
     * Features listas para pintar: [['label' => ..., 'excluded' => bool], ...].
     * En BD van como texto separado por comas, en español, y con "-" delante
     * las que el plan NO incluye. Se traducen por slug con
     * landing.plan_features.<slug>; si no hay traducción se muestra el texto
     * tal cual (así una feature nueva creada desde el panel nunca desaparece).
     */
    public function featureList(): array
    {
        $raw = $this->features;
        $items = is_array($raw) ? $raw : explode(',', (string) $raw);

        $list = [];
        foreach ($items as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }
            $excluded = str_starts_with($item, '-');
            $label = trim(ltrim($item, '-'));
            $key = 'landing.plan_features.'.\Illuminate\Support\Str::slug($label);
            if (\Illuminate\Support\Facades\Lang::has($key, null, false)) {
                $label = __($key);
            }
            $list[] = ['label' => $label, 'excluded' => $excluded];
        }

        return $list;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Planes activos, cacheados 30 min.
     */
    public static function getActivePlans()
    {
        if (app()->bound('cache')) {
            try {
                return Cache::remember('yambo_active_plans', 1800, function () {
                    return self::where('active', 1)->orderBy('sort_order')->orderBy('id')->with('role')->get();
                });
            } catch (Exception $e) {
                // Si la caché falla, se sirve la query directa.
            }
        }

        return self::where('active', 1)->orderBy('sort_order')->orderBy('id')->with('role')->get();
    }

    public static function getByName($name)
    {
        if (app()->bound('cache')) {
            try {
                return Cache::remember("yambo_plan_{$name}", 1800, function () use ($name) {
                    return self::where('name', $name)->with('role')->first();
                });
            } catch (Exception $e) {
                // Idem.
            }
        }

        return self::where('name', $name)->with('role')->first();
    }

    public static function clearCache()
    {
        if (! app()->bound('cache')) {
            return;
        }

        try {
            Cache::forget('yambo_active_plans');
            foreach (self::pluck('name') as $planName) {
                Cache::forget("yambo_plan_{$planName}");
            }
        } catch (Exception $e) {
            // Vaciar la caché nunca debe tumbar la petición.
        }
    }

    /**
     * Límite de una feature: null = ilimitado, int = tope.
     */
    public function getLimit(string $feature): ?int
    {
        $limits = $this->limits ?? [];

        if (! array_key_exists($feature, $limits)) {
            return null;
        }

        $limit = $limits[$feature];

        // -1 significa ilimitado explícito.
        if ($limit === -1) {
            return null;
        }

        return (int) $limit;
    }

    public function hasLimit(string $feature): bool
    {
        return array_key_exists($feature, $this->limits ?? []);
    }
}

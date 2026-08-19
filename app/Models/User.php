<?php

namespace App\Models;

use Devdojo\Auth\Models\User as AuthUser;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Ya no extiende Wave\User: cuelga directamente de devdojo/auth, que es el
 * paquete que de verdad da el login y que sí se mantiene. De Wave se absorbió
 * solo lo que usa el código vivo — suscripciones, caché de estado y JWT — y se
 * dejaron fuera sus traits de perfil y de features de plan, que no consumía
 * nadie tras retirar las páginas de settings.
 *
 * El JWT del APK se firma con JWT_SECRET (tymon/jwt-auth), no con APP_KEY, así
 * que los tokens emitidos siguen siendo válidos mientras esta clase implemente
 * JWTSubject.
 */
class User extends AuthUser implements JWTSubject
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    public $guard_name = 'web';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'avatar',
        'password',
        'role_id',
        'verification_code',
        'verified',
        'trial_ends_at',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // OJO: `trial_ends_at` va SIN cast a datetime a propósito. Wave lo
            // casteaba, pero el casts() de esta clase lo anulaba, así que en
            // producción siempre fue string. Castearlo ahora cambiaría el JSON
            // de /api/app-tv/{login,register,status} de "2026-05-04 19:54:28"
            // a "2026-05-04T19:54:28.000000Z", y eso lo parsea un APK ya
            // publicado. Quien lo lee usa Carbon::parse(), que traga ambos.
            'notification_preferences' => 'array',
            'social_links' => 'array',
            'privacy_settings' => 'array',
            'deletion_scheduled_at' => 'datetime',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'billable_id')->where('billable_type', 'user');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Trial vivo solo si no hay ya una suscripción de pago.
     */
    public function onTrial(): bool
    {
        if (is_null($this->trial_ends_at)) {
            return false;
        }

        return ! $this->subscriber();
    }

    public function subscriber(): bool
    {
        if (app()->bound('cache')) {
            try {
                return Cache::remember("user_subscriber_{$this->id}", 300, function () {
                    return $this->subscriptions()->where('status', 'active')->exists();
                });
            } catch (Exception $e) {
                // Si la caché falla, se responde con la query directa.
            }
        }

        return $this->subscriptions()->where('status', 'active')->exists();
    }

    public function isAdmin(): bool
    {
        if (app()->bound('cache')) {
            try {
                return Cache::remember("user_admin_{$this->id}", 600, function () {
                    return $this->hasRole('admin');
                });
            } catch (Exception $e) {
                // Idem.
            }
        }

        return $this->hasRole('admin');
    }

    /**
     * Deja al usuario con el único rol del plan indicado.
     */
    public function switchPlans(Plan $plan): void
    {
        $this->syncRoles([]);
        $this->assignRole($plan->role->name);
    }

    public function clearUserCache(): void
    {
        if (! app()->bound('cache')) {
            return;
        }

        try {
            Cache::forget("user_subscriber_{$this->id}");
            Cache::forget("user_admin_{$this->id}");

            foreach (Plan::pluck('name') as $planName) {
                Cache::forget("user_plan_{$this->id}_{$planName}");
            }
        } catch (Exception $e) {
            // Vaciar la caché nunca debe tumbar la petición.
        }
    }

    public function avatar(): string
    {
        return Storage::url($this->avatar);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $username = Str::slug($user->name, '');
                $i = 1;
                while (self::where('username', $username)->exists()) {
                    $username = Str::slug($user->name, '').$i;
                    $i++;
                }
                $user->username = $username;
            }
        });

        static::created(function ($user) {
            $user->syncRoles([]);

            $defaultRole = config('yammbo.default_user_role', 'registered');
            if (\Spatie\Permission\Models\Role::where('name', $defaultRole)->where('guard_name', 'web')->exists()) {
                $user->assignRole($defaultRole);
            }
        });
    }
}

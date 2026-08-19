<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de actividad de cuenta (logins, logouts, cambios de contraseña).
 * Absorbido de Wave\ActivityLog para poder retirar el paquete; misma tabla
 * `activity_logs`, así que convive con la clase vieja durante la migración.
 *
 * Se cayó la rama de encolado del original: dependía de un Job de Wave y
 * `ACTIVITY_LOG_QUEUE` está desactivado. Con 11 filas y un puñado de logins al
 * día, escribir en línea sobra.
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $metadata
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra una acción del usuario autenticado. Devuelve null si el registro
     * está desactivado o si no hay sesión.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function log(string $action, ?string $description = null, ?array $metadata = null): ?static
    {
        if (! config('activity.enabled', true)) {
            return null;
        }

        if (! auth()->check()) {
            return null;
        }

        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            // Detrás de Cloudflare, request()->ip() es la IP del edge.
            'ip_address' => request()->header('CF-Connecting-IP') ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}

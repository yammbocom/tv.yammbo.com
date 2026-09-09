<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class YamboAddonToken extends Model
{
    protected $table = 'yambo_addon_tokens';

    protected $fillable = ['user_id', 'token', 'revoked_at', 'last_used_at', 'hits', 'ips'];

    protected $casts = [
        'revoked_at' => 'datetime',
        'last_used_at' => 'datetime',
        'ips' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Token vigente del usuario, creándolo si no lo tiene. Si estaba revocado se
     * rota: el usuario recupera el acceso y la URL vieja que circulaba muere.
     */
    public static function forUser(int $userId): self
    {
        $row = static::firstOrCreate(
            ['user_id' => $userId],
            ['token' => static::freshToken()]
        );

        if ($row->revoked_at !== null) {
            $row->forceFill([
                'token' => static::freshToken(),
                'revoked_at' => null,
                'ips' => null,
            ])->save();
        }

        return $row;
    }

    public static function freshToken(): string
    {
        do {
            $token = Str::lower(Str::random(40));
        } while (static::where('token', $token)->exists());

        return $token;
    }
}

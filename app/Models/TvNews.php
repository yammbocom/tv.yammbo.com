<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TvNews extends Model
{
    protected $table = 'tv_news';

    protected $fillable = [
        'title', 'body', 'image_url', 'action', 'action_value', 'audience', 'genre',
        'popup', 'active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'popup' => 'boolean',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /** Publicadas ahora mismo para ese publico (all siempre incluido). */
    public function scopeLiveFor(Builder $q, string $segment): Builder
    {
        $now = now();

        return $q->where('active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->whereIn('audience', ['all', $segment]);
    }
}

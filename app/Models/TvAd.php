<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TvAd extends Model
{
    protected $table = 'tv_ads';

    protected $fillable = [
        'name', 'video_url', 'link_text', 'skip_after', 'audience', 'weight',
        'active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'skip_after' => 'integer',
        'weight' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeLiveFor(Builder $q, string $segment): Builder
    {
        $now = now();

        return $q->where('active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->whereIn('audience', ['all', $segment]);
    }
}

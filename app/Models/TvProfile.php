<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TvProfile extends Model
{
    protected $table = 'tv_profiles';

    protected $fillable = ['user_id', 'name', 'avatar', 'color', 'kids', 'position'];

    protected $casts = [
        'kids' => 'boolean',
        'color' => 'integer',
        'position' => 'integer',
    ];

    public function toApp(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar ?: '',
            'color' => $this->color,
            'kids' => $this->kids,
        ];
    }
}

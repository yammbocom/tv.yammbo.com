<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushLog extends Model
{
    protected $fillable = [
        'user_id', 'topic', 'title', 'body', 'click_url', 'image_url',
        'success', 'fcm_message_id', 'error', 'response',
    ];

    protected $casts = [
        'success' => 'boolean',
        'response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

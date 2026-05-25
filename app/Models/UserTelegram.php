<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTelegram extends Model
{

    protected $table = "user_telegram";

    protected $fillable = [
        'user_id',
        'sms_code',
        'attempt',
        'telegram_id',
        'verified_at',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'attempt_time' => 'datetime',
    ];
}

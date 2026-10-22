<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_fingerprint',
        'sms_sent_today',
        'last_sms_sent_at',
        'ip_address',
    ];

    protected $casts = [
        'last_sms_sent_at' => 'datetime',
    ];
}

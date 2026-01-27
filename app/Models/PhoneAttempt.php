<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneAttempt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'sms_sent_today',
        'last_sms_sent_at',
        'ip_address',
    ];

    protected $casts = [
        'last_sms_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

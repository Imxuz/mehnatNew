<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'user_id',
        'vacancy_id',
        'admin_id',
        'sent_id',
    ];
}

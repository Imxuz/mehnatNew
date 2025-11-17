<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacancyLog extends Model
{
    protected $fillable = [
        'admin_id',
        'model',
        'action',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

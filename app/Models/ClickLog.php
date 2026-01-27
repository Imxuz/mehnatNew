<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    protected $fillable = [
        'admin_id',
        'model',
        'action',
        'old_values',
        'new_values',
    ];
}

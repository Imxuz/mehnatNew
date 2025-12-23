<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOccupation extends Model
{
    protected $table = 'special_occupations';
    protected $fillable = [
        'admin_id',
        'occupation_id',
        'title',
        'description',
        'is_active',

    ];
}

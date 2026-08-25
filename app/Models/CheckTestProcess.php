<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckTestProcess extends Model
{
    protected $table = 'check_test_processes';

    protected $fillable = [
        'admin_id',
        'vacancy_id',
        'test_date',
        'duration',
        'duration_unit',
        'total_hours',
    ];
}

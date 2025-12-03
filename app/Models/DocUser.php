<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocUser extends Model
{
    protected $fillable = [
        'user_id',
        'dir_demand_id',
        'path',
        'adder_demands_id',

    ];

}

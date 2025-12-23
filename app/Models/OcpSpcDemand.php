<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcpSpcDemand extends Model
{
    protected $table = 'ocp_spc_demands';
    protected $fillable = [
      'special_occupation_id',
      'occupation_id',
    ];
}

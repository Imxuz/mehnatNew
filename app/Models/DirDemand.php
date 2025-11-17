<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirDemand extends Model
{
    protected $fillable = [
        'title', 'name','sort_number','type',
    ];
    public function adder_demands(){
        return $this->hasMany(AdderDemand::class,'dir_demand_id', 'id');
    }
}

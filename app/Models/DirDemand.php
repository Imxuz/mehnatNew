<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirDemand extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title', 'name','sort_number','type','formType', 'demand_type'
    ];
    public function adder_demands(){
        return $this->hasMany(AdderDemand::class,'dir_demand_id', 'id');
    }
}

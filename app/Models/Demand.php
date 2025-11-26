<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    protected $fillable = [
        'dir_demand_id',
        'adder_text'   ,
        'vacancy_id'   ,
        'score'        ,
    ];

    public function dir_demand(){
        return $this->hasOne(DirDemand::class,'id','dir_demand_id')->select('id','title','name','formType');;
    }

}

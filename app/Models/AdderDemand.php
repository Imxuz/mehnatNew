<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdderDemand extends Model
{
    //


    public function dir_demand(){
        return $this->hasOne(DirDemand::class,'id','dir_demand_id')
            ->select('id','title','sort_number','type');
    }
}

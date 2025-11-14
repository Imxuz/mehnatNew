<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        "region_id" ,
         "dir_demand_id",
         "occupation_id",
         "open_At"      ,
         "close_At"     ,
         "admin_id"     ,
         "publication"  ,
    ];

    public function demands()
    {
        return $this->hasMany(Demand::class, 'vacancy_id', 'id')
            ->select('id', 'vacancy_id', 'adder_text', 'score', 'dir_demand_id');
    }

}

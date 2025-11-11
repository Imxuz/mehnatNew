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
}

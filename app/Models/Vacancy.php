<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        "region_id" ,
         "dir_demand_id",
         "occupation_id",
         "open_at"      ,
         "close_at"     ,
         "admin_id"     ,
         "publication"  ,
    ];



    public function demands()
    {
        return $this->hasMany(Demand::class, 'vacancy_id', 'id')
            ->join('dir_demands as d', 'd.id', '=', 'demands.dir_demand_id')
            ->select(
                'demands.id',
                'demands.vacancy_id',
                'demands.adder_text',
                'demands.score',
                'demands.dir_demand_id',
                'd.formType as formType'
            );
    }
    public function occupation()
    {
        return $this->hasOne(Occupation::class, 'id', 'occupation_id')
            ->select('id', 'occupation');
    }
    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id')
            ->select('id', 'title', 'sub_region_id')
            ->with('parentRegion');
    }
    public function oClick()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->hasOne(Click::class)->whereRaw('1 = 0');
        }
        return $this->hasOne(Click::class, 'vacancy_id', 'id')
            ->select('id', 'vacancy_id', 'user_id','sent')
            ->where('user_id', $user->id);
    }
    public function clicks(){
        return $this->hasMany(Click::class,'vacancy_id','id');
    }



}

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

    protected $casts = [
        'adder_text' => 'json',
    ];
    protected $appends = [
        'adder_texts'
    ];

    public function dir_demand(){
        return $this->hasOne(DirDemand::class,'id','dir_demand_id')->select('id','title','name','formType');;
    }
    public function getAdderTextsAttribute()
    {
        $value = $this->adder_text;

        if (is_array($value)) {
            return AdderDemand::whereIn('id', $value)->get();
        }

        return AdderDemand::where('id', $value)->first();
    }

}

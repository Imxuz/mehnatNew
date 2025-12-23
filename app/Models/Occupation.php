<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    protected $fillable = [
        'occupation', 'demand'
    ];

    public function spcOccupation(){
        return $this->hasMany(SpecialOccupation::class,'occupation_id', 'id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name', 'sub_region_id','title',
    ];



    public function children()
    {
        return $this->hasMany(Region::class, 'sub_region_id', 'id')
            ->with('children');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name', 'sub_region_id','title', 'division','div_region'
    ];



    public function children()
    {
        return $this->hasMany(Region::class, 'sub_region_id', 'id')
            ->select('id','title', 'sub_region_id')
            ->with('children');
    }

    public function parentRegion()
    {
        return $this->belongsTo(Region::class, 'sub_region_id', 'id')
            ->select('id', 'title');
    }



    public function divChildren()
    {
        return $this->hasMany(Region::class, 'div_region', 'division')
            ->select('id', 'title', 'sub_region_id', 'div_region', 'division')
            ->with('divChildren');
    }

    public function divParentRegion()
    {
        return $this->belongsTo(Region::class, 'division', 'id')
            ->select('id', 'title');
    }
}


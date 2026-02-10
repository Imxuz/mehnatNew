<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocUser extends Model
{
    protected $fillable = [
        'user_id',
        'dir_demand_id',
        'path',
        'adder_demands_id',
        'check',
        'vacancy_doc_id',
        'ip_address',

    ];

    public function demand()
    {
        return $this->belongsTo(DirDemand::class, 'dir_demand_id');
    }
    public function adderDemand()
    {
        return $this->belongsTo(AdderDemand::class, 'adder_demands_id');
    }

}

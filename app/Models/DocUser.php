<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocUser extends Model
{
    use SoftDeletes;
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
        return $this->belongsTo(DirDemand::class, 'dir_demand_id')->select(['title','name','id','forWho','formType','type']);
    }
    public function adderDemand()
    {
        return $this->belongsTo(AdderDemand::class, 'adder_demands_id');
    }

}

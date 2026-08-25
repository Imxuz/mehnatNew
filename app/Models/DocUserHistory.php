<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocUserHistory extends Model
{
    use SoftDeletes;
    protected $table = "doc_user_history";
    protected $fillable=[
        'click_id',
        'user_id',
        'dir_demand_id',
        'path',
        'adder_demands_id',
        'doc_info',
        'description',
        'ip_address',
    ];

    public function demand()
    {
        return $this->belongsTo(DirDemand::class, 'dir_demand_id');
    }
}

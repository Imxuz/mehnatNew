<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Click extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'vacancy_id',
        'is_manual',
        'created_by_admin_id',
        'basis_file',
        'basis_comment',
        'admin_id',
        'sent_id',
        'comment',
        'sent',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id', 'id');
    }

    public function doc_histories(){
        return $this->hasMany(DocUserHistory::class, 'click_id', 'id');
    }

    public function clickVacancy(){
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }

    public function userDocs(){
        return $this->hasMany(DocUser::class, 'user_id', 'user_id')
            ->select(['id','user_id','path','dir_demand_id','check'])
            ->with('demand');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'user_id',
        'vacancy_id',
        'admin_id',
        'sent_id',
        'comment',
        'sent',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function doc_histories(){
        return $this->hasMany(DocUserHistory::class, 'click_id', 'id');
    }

    public function clickVacancy(){
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }

}

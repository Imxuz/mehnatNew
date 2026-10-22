<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacancySpecialDemand extends Model
{
    protected $table = 'vacancy_special_demands';

    protected $fillable = [
        'vacancy_id',
        'dir_demand_id',
        'text',
    ];

    public function dir_demand()
    {
        return $this->hasOne(DirDemand::class, 'id', 'dir_demand_id')->select('id', 'title', 'name', 'formType');
    }

    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }
}

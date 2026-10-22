<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    protected $fillable = [
        'admin_id',
        'click_id',
        'model',
        'action',
        'old_values',
        'new_values',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function click()
    {
        return $this->belongsTo(Click::class, 'click_id');
    }

    public function getOldValuesAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : $value;
        }
        return $value ?: [];
    }

    public function getNewValuesAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : $value;
        }
        return $value ?: [];
    }

    public function setOldValuesAttribute($value)
    {
        $this->attributes['old_values'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function setNewValuesAttribute($value)
    {
        $this->attributes['new_values'] = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}

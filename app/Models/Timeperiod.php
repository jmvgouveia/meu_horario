<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeperiod extends Model
{
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'active',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_timeperiod');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeperiod extends Model
{
    protected $fillable = [
        'description',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_timeperiod');
    }
}

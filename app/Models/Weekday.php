<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weekday extends Model
{
    protected $fillable = [
        'weekday',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_weekday');
    }
}

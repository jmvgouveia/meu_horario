<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $fillable = [
        'name',
        'id_course',
        'year',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    /* public function registration()
    {
        return $this->hasMany(Registration::class, 'id_class');
    } */

    public function classes()
    {
        return $this->belongsToMany(
            \App\Models\Classes::class,
            'schedule_class',      // nome da tabela pivot
            'schedule_id',
            'class_id'
        );
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'id_building');
    }
}

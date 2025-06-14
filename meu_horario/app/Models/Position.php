<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
       'name',
       'description',
       'reduction_l',
       'reduction_nl',

    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_positions', 'id_teacher', 'id_position');
    }
}

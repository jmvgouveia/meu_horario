<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeReduction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'value_l',
        'value_nl',
        'eligibility'
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_time_reductions', 'id_teacher', 'id_time_reduction');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherHourCounter extends Model
{
    protected $fillable = [
        'id_teacher',
        'workload',
        'teaching_load',
        'non_teaching_load',
        'authorized_overtime',
        'id_schoolyear',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }

    public function schoolyear()
    {
        return $this->belongsTo(SchoolYear::class, 'id_schoolyear');
    }
}

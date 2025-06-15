<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'type',
    ];

    public function courses()
    {
        return $this->hasMany(CourseSubject::class, 'id_subject');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects', 'id_subject', 'id_teacher')
            ->withPivot('id_schoolyear');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_subject');
    }
}

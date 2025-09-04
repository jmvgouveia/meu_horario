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
        'status',
        'student_can_enroll'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_subjects', 'id_subject', 'id_course')->withTimestamps();
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

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}

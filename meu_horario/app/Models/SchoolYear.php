<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schoolyear extends Model
{
    protected $fillable = [
        'schoolyear',
        'start_date',
        'end_date',
        'active'
    ];

    public function courseStudents()
    {
        return $this->hasMany(CourseSubject::class, 'id_schoolyear');
    }

    public function courses()
    {
        return $this->hasManyThrough(
            Course::class,
            CourseSubject::class,
            'id_schoolyear',
            'id',
            'id',
            'id_course'
        )->distinct();
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            CourseSubject::class,
            'id_schoolyear',
            'id',
            'id',
            'id_subject'
        )->distinct();
    }
}

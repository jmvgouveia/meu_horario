<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $table = 'schoolyears';

    protected $fillable = [
        'schoolyear',
        'start_date',
        'end_date',
        'start_date_registration',
        'end_date_registration',
        'active'
    ];


    public function courseSubjects()
    {
        return $this->hasMany(CourseSubject::class, 'id_schoolyear');
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

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_schoolyear');
    }
}

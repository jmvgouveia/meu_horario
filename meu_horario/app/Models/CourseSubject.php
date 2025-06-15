<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSubject extends Model
{
    protected $fillable = [
        'id_course',
        'id_subject',
        'id_schoolyear',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }
    
    public function schoolyear()
    {
        return $this->belongsTo(SchoolYear::class, 'id_schoolyear');
    }
}

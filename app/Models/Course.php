<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'name',
    ];

    public function subjects()
    {
        return $this->hasMany(CourseSubject::class, 'id_course');
    }

    public function subjectsPerSchoolYear($schoolyearId)
    {
        return $this->courseSubjects()
            ->where('id_schoolyear', $schoolyearId)
            ->with('subject')
            ->get()
            ->pluck('subject');
    }
}

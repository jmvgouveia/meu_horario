<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveSchoolYear;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use BelongsToActiveSchoolYear;

    protected $fillable = [
        'id_student',
        'id_course',
        'id_schoolyear',
        'id_class',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function schoolyear()
    {
        return $this->belongsTo(SchoolYear::class, 'id_schoolyear');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class', 'id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'registrations_subjects', 'id_registration', 'id_subject')
            ->using(RegistrationSubject::class)
            ->withPivot(['id', 'shift', 'id_schedule'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveSchoolYear;
use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    use BelongsToActiveSchoolYear;

    protected $fillable = [
        'id_teacher',
        'id_subject',
        'id_schoolyear',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
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

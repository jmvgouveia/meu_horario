<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'id_schoolyear',
        'id_timeperiod',
        'id_room',
        'id_teacher',
        'id_weekday',
        'id_subject',
        'shift',
        'status'
    ];

    public function schoolyear()
    {
        return $this->belongsTo(SchoolYear::class, 'id_schoolyear');
    }

    public function timeperiod()
    {
        return $this->belongsTo(Timeperiod::class, 'id_timeperiod');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }

    public function weekday()
    {
        return $this->belongsTo(Weekday::class, 'id_weekday');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'schedules_students', 'id_schedule', 'id_student');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'schedules_classes', 'id_schedule', 'id_class');
    }
    public function requests()
    {
        return $this->hasMany(ScheduleRequest::class, 'id_new_schedule');
    }
}

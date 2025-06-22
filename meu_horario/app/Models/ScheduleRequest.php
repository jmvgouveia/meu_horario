<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRequest extends Model
{
    protected $fillable = [
        'id_schedule',
        'id_teacher',
        'id_new_schedule',
        'justification',
        'status',
        'response',
        'responded_at',
        'response_coord',
        'scaled_justification',
    ];

    public function scheduleConflict()
    {
        return $this->belongsTo(Schedule::class, 'id_schedule');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }

    public function scheduleNew()
    {
        return $this->belongsTo(Schedule::class, 'id_new_schedule');
    }

    public function requester()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }
}

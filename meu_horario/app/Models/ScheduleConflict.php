<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleConflict extends Model
{
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function weekday()
    {
        return $this->belongsTo(Weekday::class, 'id_weekday');
    }

    public function timePeriod()
    {
        return $this->belongsTo(Timeperiod::class, 'id_time_period');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBlockedHours extends Model
{
    protected $fillable = [
        'id_building',
        'id_room',
        'id_weekday',
        'id_timeperiod',
        'description',
        'active',
        'id_schoolyear',

    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_room');
    }
    public function weekday(): BelongsTo
    {
        return $this->belongsTo(Weekday::class, 'id_weekday');
    }
    public function timeperiod(): BelongsTo
    {
        return $this->belongsTo(Timeperiod::class, 'id_timeperiod');
    }
    public function schoolyear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'id_schoolyear');
    }
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'id_building');
    }
}

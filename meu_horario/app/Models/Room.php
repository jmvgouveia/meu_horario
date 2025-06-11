<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'name',
        'description',
        'id_building',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'id_building');
    }
}

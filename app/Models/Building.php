<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'name',
        'address',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'id_building');
    }
}

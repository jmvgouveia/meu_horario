<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'address',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}

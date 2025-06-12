<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    protected $fillable = [
        'gender',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'id_gender');
    }
}

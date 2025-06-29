<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'number',
        'name',
        'id_gender',
        'birthdate',
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'id_student', 'id');
    }
}

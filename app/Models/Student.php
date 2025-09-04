<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'number',
        'name',
        'email',
        'id_gender',
        'birthdate',
        'user_id',
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'id_student', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

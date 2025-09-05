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
    public function schedules()
    {
        // Confirma o nome da pivot: 'schedule_students' (plural) é o mais comum.
        // Se na tua BD for 'schedule_student', troca o segundo argumento.
        return $this->belongsToMany(
            Schedule::class,
            'schedules',   // <- pivot table
            'id_student',          // <- FK para Student na pivot
            'id_schedule'          // <- FK para Schedule na pivot
        )->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $fillable = [
        'gender',
    ];

    public function teacher()
    {
        return $this->hasMany(Teacher::class, 'id_gender');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'id_gender');
    }
}

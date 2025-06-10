<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'number',
        'name',
        'gender_id',
        'birthdate',
    ];

    public function genders()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }
}

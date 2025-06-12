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

    public function genders()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }
}

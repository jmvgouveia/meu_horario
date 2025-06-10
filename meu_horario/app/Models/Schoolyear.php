<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schoolyear extends Model
{
    protected $fillable = [
        'schoolyear',
        'start_date',
        'end_date',
        'active'
    ];
}

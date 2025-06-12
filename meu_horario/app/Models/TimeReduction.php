<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeReduction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'value_l',
        'value_nl',
        'eligibility'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
       'name',
       'description',
       'reduction_l',
       'reduction_nl',

    ];
}

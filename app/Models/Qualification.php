<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $fillable = [
       'name',
       'description',
       'qnq_level',
       'sort_order',
       'is_active',
    ];


    protected $casts = [
        'is_active' => 'boolean',
        'qnq_level' => 'integer',
        'sort_order' => 'integer',
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $fillable = [
        'name',
        'id_course',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}

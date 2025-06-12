<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'name',
    ];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'id_course')
            ->using(CourseSubject::class)
            ->withPivot(['id_schoolyear'])
            ->withTimestamps();
    }
}

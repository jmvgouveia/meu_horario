<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'type',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'id_subject')
            ->using(CourseSubject::class)
            ->withPivot(['id_schoolyear'])
            ->withTimestamps();
    }
}

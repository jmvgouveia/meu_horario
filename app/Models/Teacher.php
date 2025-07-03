<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'number',
        'name',
        'acronym',
        'birthdate',
        'startingdate',
        'id_nationality',
        'id_gender',
        'id_qualification',
        'id_department',
        'id_professionalrelationship',
        'id_contractualrelationship',
        'id_salaryscale',
        'id_user',
    ];

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'id_nationality');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }

    public function qualification()
    {
        return $this->belongsTo(Qualification::class, 'id_qualification');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_department');
    }

    public function professionalrelationship()
    {
        return $this->belongsTo(ProfessionalRelationship::class, 'id_professionalrelationship');
    }

    public function contractualrelationship()
    {
        return $this->belongsTo(ContratualRelationship::class, 'id_contractualrelationship');
    }

    public function salaryscale()
    {
        return $this->belongsTo(SalaryScale::class, 'id_salaryscale');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'id_teacher', 'id_subject')
            ->withPivot('id_schoolyear');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'teacher_positions', 'id_teacher', 'id_position')->withPivot('id_schoolyear');
    }

    public function timeReductions()
    {
        return $this->belongsToMany(TimeReduction::class, 'teacher_time_reductions', 'id_teacher', 'id_time_reduction')->withPivot('id_schoolyear');
    }
    public function hourCounter()
    {
        return $this->hasOne(TeacherHourCounter::class, 'id_teacher');
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_teacher');
    }
    public function updateHourCounterFromReductions(): void
    {
        $letiva = 0;
        $naoLetiva = 0;

        // Reduções por cargo
        foreach ($this->positions as $position) {
            $letiva += $position->reduction_l ?? 0;
            $naoLetiva += $position->reduction_nl ?? 0;
        }

        // Reduções por tempo de serviço
        foreach ($this->timeReductions as $reduction) {
            $letiva += $reduction->value_l ?? 0;
            $naoLetiva += $reduction->value_nl ?? 0;
        }

        // Atualiza o contador
        $counter = $this->hourCounter()->firstOrCreate([]);

        $counter->update([
            'teaching_load' => max(0, $counter->default_teaching_load - $letiva),
            'non_teaching_load' => max(0, $counter->default_non_teaching_load - $naoLetiva),
        ]);
    }
}

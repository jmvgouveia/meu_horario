<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// class RegistrationSubject extends Model
// {
//     protected $table = 'registrations_subjects';

//     protected $fillable = [
//         'shift',
//     ];



//     public function subjects()
//     {
//         return $this->belongsTo(Subject::class, 'id_subject');
//     }

//     public function registration()
//     {
//         return $this->belongsTo(Registration::class, 'id_registration');
//     }

//     // Relação para chegar ao student
//     public function student()
//     {
//         return $this->hasOneThrough(
//             Student::class,       // Modelo final
//             Registration::class,  // Modelo intermediário
//             'id',                 // Chave primária da tabela Registration
//             'id',                 // Chave primária da tabela Student
//             'id_registration',    // Foreign key da RegistrationSubject → Registration
//             'id_student'          // Foreign key da Registration → Student
//         );
//     }

//     public function schoolyear()
//     {
//         return $this->hasOneThrough(
//             SchoolYear::class,    // modelo final
//             Registration::class,  // modelo intermediário
//             'id_schoolyear',      // FK na tabela registrations → schoolyears
//             'id',                 // PK na tabela schoolyears
//             'id_registration',    // FK na tabela registrations_subjects → registrations
//             'id'                  // PK na tabela registrations
//         );
//     }
//     public function hasMultipleShifts(): bool
//     {
//         return $this->availableShifts()->count() > 1;
//     }

//     public function availableShifts()
//     {
//         $classId = $this->registration->id_class;

//         return \App\Models\Schedule::whereHas('classes', function ($q) use ($classId) {
//             $q->where('classes.id', $classId);
//         })
//             ->where('id_subject', $this->id_subject) // opcional: só para mesma disciplina
//             ->where('id_schoolyear', $this->registration->id_schoolyear) // opcional: só para o ano ativo
//             ->get();
//     }

//     public function availableSchedules()
//     {
//         return $this->belongsToMany(
//             \App\Models\Schedule::class,
//             'schedules_classes',
//             'id_class',
//             'id_schedule'
//         );
//     }
//     public function schedules()
//     {
//         return $this->hasMany(\App\Models\Schedule::class, 'subject_id');
//     }

//     public function schedule()
//     {
//         return $this->belongsTo(Schedule::class, 'id_schedule');
//     }
// }


class RegistrationSubject extends Model
{
    protected $table = 'registrations_subjects';

    protected $fillable = [
        'shift',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'id_registration');
    }

    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            Registration::class,
            'id',              // PK em registrations
            'id',              // PK em students
            'id_registration', // FK em registrations_subjects
            'id_student'       // FK em registrations
        );
    }

    public function schoolyear()
    {
        return $this->hasOneThrough(
            SchoolYear::class,
            Registration::class,
            'id',              // PK em registrations
            'id',              // PK em schoolyears
            'id_registration', // FK em registrations_subjects
            'id_schoolyear'    // FK em registrations
        );
    }

    public function hasMultipleShifts(): bool
    {
        return $this->availableShifts()->count() > 1;
    }

    public function availableShifts()
    {
        $classId = $this->registration->id_class;

        return \App\Models\Schedule::whereHas('classes', function ($q) use ($classId) {
            $q->where('classes.id', $classId);
        })
            ->where('id_subject', $this->id_subject)
            ->where('id_schoolyear', $this->registration->id_schoolyear)
            ->get();
    }

    public function selectedSchedule()
    {
        return $this->belongsTo(\App\Models\Schedule::class, 'shift'); // 'shift' guarda o ID do schedule
    }
}

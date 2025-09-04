<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        // Só cria se ainda não existir user_id
        if (!$student->user_id) {
            $user = User::create([
                'name' => $student->name,
                'email' => $student->email,
                'password' => bcrypt($student->number . 'CEAM'), // senha: numeroCEAM
            ]);

            $user->assignRole('aluno');

            $student->update(['user_id' => $user->id]);
        }
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        //
    }
}

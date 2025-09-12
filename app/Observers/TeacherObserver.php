<?php

namespace App\Observers;

use App\Models\Teacher;
use App\Models\User;

class TeacherObserver
{
    /**
     * Handle the Teacher "created" event.
     */
    public function created(Teacher $teacher): void
    {
        // Só cria se ainda não existir user_id
        if (!$teacher->user_id) {
            $user = User::create([
                'name'     => $teacher->name,
                'email'    => $teacher->email, // garante que a coluna 'email' existe em teachers
                'password' => bcrypt($teacher->number . 'CEAM'), // senha: numeroCEAM
            ]);

            $user->assignRole('professor'); // atribui o perfil

            $teacher->update(['user_id' => $user->id]);
        }
    }

    /**
     * Handle the Teacher "updated" event.
     */
    public function updated(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "deleted" event.
     */
    public function deleted(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "restored" event.
     */
    public function restored(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "force deleted" event.
     */
    public function forceDeleted(Teacher $teacher): void
    {
        //
    }
}

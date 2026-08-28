<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->hasRole('Aluno') && $user->checkPermissionTo('view-any Schedule');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        return ! $user->hasRole('Aluno')
            && $user->checkPermissionTo('view Schedule')
            && (! $user->isTeacher() || $this->belongsToTeacher($user, $schedule));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ! $user->hasRole('Aluno') && $user->checkPermissionTo('create Schedule');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        return ! $user->hasRole('Aluno')
            && $user->checkPermissionTo('update Schedule')
            && (! $user->isTeacher() || $this->belongsToTeacher($user, $schedule));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete Schedule');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete-any Schedule');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Schedule $schedule): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore Schedule');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore-any Schedule');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Schedule $schedule): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('replicate Schedule');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('reorder Schedule');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Schedule $schedule): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete Schedule');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete-any Schedule');
    }

    public function export(User $user): bool
    {
        return false;
    }

    private function belongsToTeacher(User $user, Schedule $schedule): bool
    {
        $teacherId = $user->teacher?->getKey();

        return $teacherId !== null && (int) $schedule->id_teacher === (int) $teacherId;
    }

    private function canManageGlobally(User $user): bool
    {
        return ! $user->isTeacher() && ! $user->hasRole('Aluno');
    }
}

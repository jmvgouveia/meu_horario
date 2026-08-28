<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('view-any Student');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->hasRole('Aluno')) {
            return $this->belongsToUser($user, $student);
        }

        return $this->canManageGlobally($user) && $user->checkPermissionTo('view Student');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('create Student');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        if ($user->hasRole('Aluno')) {
            return $this->belongsToUser($user, $student);
        }

        return $this->canManageGlobally($user) && $user->checkPermissionTo('update Student');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete Student');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete-any Student');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Student $student): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore Student');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore-any Student');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Student $student): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('replicate Student');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('reorder Student');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Student $student): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete Student');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete-any Student');
    }

    private function belongsToUser(User $user, Student $student): bool
    {
        return $student->user_id !== null && (int) $student->user_id === (int) $user->getKey();
    }

    private function canManageGlobally(User $user): bool
    {
        return ! $user->isTeacher() && ! $user->hasRole('Aluno');
    }
}

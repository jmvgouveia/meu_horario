<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('view-any Teacher');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        if ($user->isTeacher()) {
            return $this->belongsToUser($user, $teacher);
        }

        return $this->canManageGlobally($user) && $user->checkPermissionTo('view Teacher');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('create Teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        if ($user->isTeacher()) {
            return $this->belongsToUser($user, $teacher);
        }

        return $this->canManageGlobally($user) && $user->checkPermissionTo('update Teacher');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete Teacher');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('delete-any Teacher');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Teacher $teacher): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore Teacher');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('restore-any Teacher');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Teacher $teacher): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('replicate Teacher');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('reorder Teacher');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete Teacher');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->canManageGlobally($user) && $user->checkPermissionTo('force-delete-any Teacher');
    }

    private function belongsToUser(User $user, Teacher $teacher): bool
    {
        return $teacher->id_user !== null && (int) $teacher->id_user === (int) $user->getKey();
    }

    private function canManageGlobally(User $user): bool
    {
        return ! $user->isTeacher() && ! $user->hasRole('Aluno');
    }
}

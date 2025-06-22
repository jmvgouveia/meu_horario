<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TeacherHourCounter;
use App\Models\User;

class TeacherHourCounterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TeacherHourCounter');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('view TeacherHourCounter');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TeacherHourCounter');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('update TeacherHourCounter');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('delete TeacherHourCounter');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TeacherHourCounter');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('restore TeacherHourCounter');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TeacherHourCounter');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('replicate TeacherHourCounter');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TeacherHourCounter');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TeacherHourCounter $teacherhourcounter): bool
    {
        return $user->checkPermissionTo('force-delete TeacherHourCounter');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TeacherHourCounter');
    }
}

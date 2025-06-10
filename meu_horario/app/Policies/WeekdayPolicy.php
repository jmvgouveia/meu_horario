<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Weekday;
use App\Models\User;

class WeekdayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Weekday');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('view Weekday');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Weekday');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('update Weekday');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('delete Weekday');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Weekday');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('restore Weekday');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Weekday');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('replicate Weekday');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Weekday');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Weekday $weekday): bool
    {
        return $user->checkPermissionTo('force-delete Weekday');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Weekday');
    }
}

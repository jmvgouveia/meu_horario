<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Timeperiod;
use App\Models\User;

class TimeperiodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Timeperiod');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('view Timeperiod');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Timeperiod');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('update Timeperiod');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('delete Timeperiod');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Timeperiod');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('restore Timeperiod');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Timeperiod');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('replicate Timeperiod');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Timeperiod');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Timeperiod $timeperiod): bool
    {
        return $user->checkPermissionTo('force-delete Timeperiod');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Timeperiod');
    }
}

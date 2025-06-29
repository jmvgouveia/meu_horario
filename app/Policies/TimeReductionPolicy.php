<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TimeReduction;
use App\Models\User;

class TimeReductionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TimeReduction');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('view TimeReduction');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TimeReduction');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('update TimeReduction');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('delete TimeReduction');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TimeReduction');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('restore TimeReduction');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TimeReduction');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('replicate TimeReduction');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TimeReduction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimeReduction $timereduction): bool
    {
        return $user->checkPermissionTo('force-delete TimeReduction');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TimeReduction');
    }
}

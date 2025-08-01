<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\SalaryScale;
use App\Models\User;

class SalaryScalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any SalaryScale');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('view SalaryScale');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create SalaryScale');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('update SalaryScale');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('delete SalaryScale');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any SalaryScale');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('restore SalaryScale');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any SalaryScale');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('replicate SalaryScale');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder SalaryScale');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SalaryScale $salaryscale): bool
    {
        return $user->checkPermissionTo('force-delete SalaryScale');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any SalaryScale');
    }
}

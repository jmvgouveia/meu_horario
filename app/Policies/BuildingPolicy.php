<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Building;
use App\Models\User;

class BuildingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Building');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('view Building');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Building');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('update Building');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('delete Building');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Building');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('restore Building');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Building');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('replicate Building');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Building');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Building $building): bool
    {
        return $user->checkPermissionTo('force-delete Building');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Building');
    }
}

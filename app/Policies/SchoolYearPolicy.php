<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any SchoolYear');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('view SchoolYear');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create SchoolYear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('update SchoolYear');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('delete SchoolYear');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any SchoolYear');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('restore SchoolYear');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any SchoolYear');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('replicate SchoolYear');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder SchoolYear');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SchoolYear $schoolyear): bool
    {
        return $user->checkPermissionTo('force-delete SchoolYear');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any SchoolYear');
    }
}

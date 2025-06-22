<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Classes;
use App\Models\User;

class ClassesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Classes');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('view Classes');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Classes');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('update Classes');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('delete Classes');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Classes');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('restore Classes');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Classes');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('replicate Classes');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Classes');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Classes $classes): bool
    {
        return $user->checkPermissionTo('force-delete Classes');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Classes');
    }
}

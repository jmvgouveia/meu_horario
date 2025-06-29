<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ContratualRelationship;
use App\Models\User;

class ContratualRelationshipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ContratualRelationship');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('view ContratualRelationship');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ContratualRelationship');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('update ContratualRelationship');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('delete ContratualRelationship');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ContratualRelationship');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('restore ContratualRelationship');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ContratualRelationship');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('replicate ContratualRelationship');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ContratualRelationship');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContratualRelationship $contratualrelationship): bool
    {
        return $user->checkPermissionTo('force-delete ContratualRelationship');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ContratualRelationship');
    }
}

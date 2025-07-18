<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ProfessionalRelationship;
use App\Models\User;

class ProfessionalRelationshipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ProfessionalRelationship');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('view ProfessionalRelationship');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ProfessionalRelationship');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('update ProfessionalRelationship');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('delete ProfessionalRelationship');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ProfessionalRelationship');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('restore ProfessionalRelationship');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ProfessionalRelationship');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('replicate ProfessionalRelationship');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ProfessionalRelationship');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProfessionalRelationship $professionalrelationship): bool
    {
        return $user->checkPermissionTo('force-delete ProfessionalRelationship');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ProfessionalRelationship');
    }
}

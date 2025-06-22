<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CourseSubject;
use App\Models\User;

class CourseSubjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CourseSubject');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('view CourseSubject');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CourseSubject');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('update CourseSubject');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('delete CourseSubject');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CourseSubject');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('restore CourseSubject');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CourseSubject');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('replicate CourseSubject');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CourseSubject');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CourseSubject $coursesubject): bool
    {
        return $user->checkPermissionTo('force-delete CourseSubject');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CourseSubject');
    }
}

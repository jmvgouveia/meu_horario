<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TeacherSubject;
use App\Models\User;

class TeacherSubjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TeacherSubject');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('view TeacherSubject');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TeacherSubject');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('update TeacherSubject');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('delete TeacherSubject');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TeacherSubject');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('restore TeacherSubject');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TeacherSubject');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('replicate TeacherSubject');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TeacherSubject');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TeacherSubject $teachersubject): bool
    {
        return $user->checkPermissionTo('force-delete TeacherSubject');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TeacherSubject');
    }
}

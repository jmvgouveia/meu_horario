<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view Registration', 'view-any Registration', 'create Registration', 'update Registration', 'delete Registration', 'delete-any Registration',
            'view TeacherSubject', 'view-any TeacherSubject', 'create TeacherSubject', 'update TeacherSubject', 'delete TeacherSubject', 'delete-any TeacherSubject',
            'view CourseSubject', 'view-any CourseSubject', 'create CourseSubject', 'update CourseSubject', 'delete CourseSubject', 'delete-any CourseSubject',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'Secretaria', 'guard_name' => 'web'])
            ->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::where(['name' => 'Secretaria', 'guard_name' => 'web'])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

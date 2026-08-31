<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar o cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 🔧 Permissões fixas manuais (não ligadas a resources)
        $customPermissions = [
            'aprovar trocas',
            'ver relatórios',
            'view teacher students',
        ];

        $secretariaPermissions = [
            'view Registration', 'view-any Registration', 'create Registration', 'update Registration', 'delete Registration', 'delete-any Registration',
            'view TeacherSubject', 'view-any TeacherSubject', 'create TeacherSubject', 'update TeacherSubject', 'delete TeacherSubject', 'delete-any TeacherSubject',
            'view CourseSubject', 'view-any CourseSubject', 'create CourseSubject', 'update CourseSubject', 'delete CourseSubject', 'delete-any CourseSubject',
        ];

        foreach (array_merge($customPermissions, $secretariaPermissions) as $perm) {
            Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web']);
        }

        // ⚙️ Gerar permissões automáticas a partir dos Filament Resources
        foreach (Filament::getResources() as $resource) {
            foreach ($resource::getPermissions() as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }
        }

        // 🧱 Criar roles e associar permissões
        $roles = [
            'Super Admin' => Permission::all()->pluck('name')->toArray(),
            'Professor' => [
                'view_schedule',
                'create_schedule',
                'aprovar trocas',
                'view teacher students',
            ],
            'Gestor Conflitos' => [
                'view_any_schedule',
                'view_schedule',
                'aprovar trocas',
            ],
            'Recursos Humanos' => [
                'view_any_user',
                'create_user',
                'update_user',
                'delete_user',
            ],
            'Aluno' => [],
            'Secretaria' => $secretariaPermissions,
        ];

        foreach ($roles as $role => $permissions) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($permissions);
        }

        // 👤 Atribuir Super Admin ao primeiro utilizador (opcional)
        $user = User::first();
        if ($user && !$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
    }
}

<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Observers\StudentObserver;
use App\Models\Student;
use App\Models\Teacher;
use App\Observers\TeacherObserver;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale(session('locale', 'pt_PT')); // ou cookie, user preference, etc.

        // Registar cores adicionais a usar no projeto
        FilamentColor::register([
            'forest_green' => Color::hex('#228B22'),
            'blue_mh' => Color::hex('#0094ee'),
            'green_aprovado' => Color::hex('#065f46'),
            'blue_troca' => Color::hex('#2563eb'),
            'red_rejeitado' => Color::hex('#dc2626'),
            'purple_escalado' => Color::hex('#7c3aed'),
            'yellow_pendente' => Color::hex('#ca8a04'),
        ]);

        // Políticas de permissões de utilizadores e Super Admin
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        Student::observe(StudentObserver::class);
        //  Teacher::observe(TeacherObserver::class);
    }
}

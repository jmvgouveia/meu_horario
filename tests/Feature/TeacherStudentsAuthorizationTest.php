<?php

namespace Tests\Feature;

use App\Filament\Resources\TeacherStudentsResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherStudentsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_can_access_teacher_students_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('Professor'));

        $this->actingAs($user);

        $this->assertTrue(TeacherStudentsResource::canAccess());
        $this->get('/maestro/teacher-students')->assertOk();
    }

    public function test_user_without_permission_cannot_access_teacher_students_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('Recursos Humanos'));

        $this->actingAs($user);

        $this->assertFalse(TeacherStudentsResource::canAccess());
        $this->get('/maestro/teacher-students')->assertForbidden();
    }

    public function test_super_admin_can_access_without_explicit_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('Super Admin'));

        $this->actingAs($user);

        $this->assertTrue(TeacherStudentsResource::canAccess());
        $this->get('/maestro/teacher-students')->assertOk();
    }
}

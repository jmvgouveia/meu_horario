<?php

namespace Tests\Feature;

use App\Filament\Imports\TeacherImporter;
use App\Models\ContratualRelationship;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\ProfessionalRelationship;
use App\Models\Qualification;
use App\Models\SalaryScale;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_creates_teacher_user_and_optional_relations(): void
    {
        Role::findOrCreate('Professor');
        $user = User::factory()->create();
        $gender = Gender::firstOrFail();
        $nationality = Nationality::create(['name' => 'Portuguesa', 'acronym' => 'PT']);
        $qualification = Qualification::create(['name' => 'Licenciatura']);
        $department = Department::create(['name' => 'Cordas']);
        $professional = ProfessionalRelationship::create(['name' => 'Docente']);
        $contractual = ContratualRelationship::create(['name' => 'Contrato']);
        $salaryScale = SalaryScale::create(['scale' => 'Escala A']);
        $import = Import::create([
            'file_name' => 'teachers.csv',
            'file_path' => 'teachers.csv',
            'importer' => TeacherImporter::class,
            'total_rows' => 1,
            'user_id' => $user->getKey(),
        ]);
        $map = collect([
            'number', 'name', 'acronym', 'email', 'birthdate', 'startingdate',
            'id_gender', 'id_nationality', 'id_qualification', 'id_department',
            'id_professionalrelationship', 'id_contractualrelationship', 'id_salaryscale',
        ])->mapWithKeys(fn (string $column): array => [$column => $column])->all();
        $importer = new TeacherImporter($import, $map, []);

        $importer([
            'number' => '2001',
            'name' => '  Professor Teste  ',
            'acronym' => 'pt',
            'email' => 'professor@example.test',
            'birthdate' => '15/01/1980',
            'startingdate' => '01-09-2020',
            'id_gender' => $gender->gender,
            'id_nationality' => $nationality->name,
            'id_qualification' => $qualification->name,
            'id_department' => $department->name,
            'id_professionalrelationship' => $professional->name,
            'id_contractualrelationship' => $contractual->name,
            'id_salaryscale' => $salaryScale->scale,
        ]);

        $teacher = Teacher::where('number', 2001)->with('user')->firstOrFail();

        $this->assertSame('Professor Teste', $teacher->name);
        $this->assertSame('PT', $teacher->acronym);
        $this->assertSame('1980-01-15', $teacher->birthdate->format('Y-m-d'));
        $this->assertSame('2020-09-01', $teacher->startingdate->format('Y-m-d'));
        $this->assertSame('professor@example.test', $teacher->user->email);
        $this->assertTrue($teacher->user->hasRole('Professor'));
        $this->assertSame($gender->getKey(), $teacher->id_gender);
        $this->assertSame($nationality->getKey(), $teacher->id_nationality);
        $this->assertSame($qualification->getKey(), $teacher->id_qualification);
        $this->assertSame($department->getKey(), $teacher->id_department);
        $this->assertSame($professional->getKey(), $teacher->id_professionalrelationship);
        $this->assertSame($contractual->getKey(), $teacher->id_contractualrelationship);
        $this->assertSame($salaryScale->getKey(), $teacher->id_salaryscale);
    }
}

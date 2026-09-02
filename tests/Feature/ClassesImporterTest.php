<?php

namespace Tests\Feature;

use App\Filament\Imports\ClassesImporter;
use App\Models\Building;
use App\Models\Classes;
use App\Models\Course;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassesImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_syncs_allowed_buildings_without_filling_a_nonexistent_column(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Curso de teste']);
        $firstBuilding = Building::create(['name' => 'Sede', 'address' => 'A']);
        $secondBuilding = Building::create(['name' => 'SMT', 'address' => 'B']);
        $import = Import::create([
            'file_name' => 'classes.csv',
            'file_path' => 'classes.csv',
            'importer' => ClassesImporter::class,
            'total_rows' => 1,
            'user_id' => $user->getKey(),
        ]);
        $importer = new ClassesImporter($import, [
            'name' => 'name',
            'id_course' => 'id_course',
            'year' => 'year',
            'id_buildings' => 'id_buildings',
        ], []);

        $importer([
            'name' => 'CPAEI',
            'id_course' => $course->getKey(),
            'year' => '1',
            'id_buildings' => "{$firstBuilding->getKey()}|{$secondBuilding->getKey()}",
        ]);

        $class = Classes::where('name', 'CPAEI')->firstOrFail();

        $this->assertSame([
            $firstBuilding->getKey(),
            $secondBuilding->getKey(),
        ], $class->buildings()->pluck('buildings.id')->all());
    }
}

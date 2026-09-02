<?php

namespace Tests\Feature;

use App\Filament\Imports\TimePeriodImporter;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimePeriodImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_creates_time_period(): void
    {
        $user = User::factory()->create();
        $import = Import::create([
            'file_name' => 'timeperiods.csv',
            'file_path' => 'timeperiods.csv',
            'importer' => TimePeriodImporter::class,
            'total_rows' => 1,
            'user_id' => $user->getKey(),
        ]);
        $map = [
            'description' => 'description',
            'start_time' => 'start_time',
            'end_time' => 'end_time',
            'active' => 'active',
        ];

        $importer = new TimePeriodImporter($import, $map, []);

        $importer([
            'description' => 'Primeiro tempo',
            'start_time' => '08:00',
            'end_time' => '08:45',
            'active' => 'True',
        ]);

        $this->assertDatabaseHas('timeperiods', [
            'description' => 'Primeiro tempo',
            'start_time' => '08:00',
            'end_time' => '08:45',
            'active' => true,
        ]);
    }
}

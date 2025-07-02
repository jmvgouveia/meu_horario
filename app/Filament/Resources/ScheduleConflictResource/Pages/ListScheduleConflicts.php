<?php

namespace App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Filament\Resources\ScheduleConflictResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleConflicts extends ListRecords
{
    protected static string $resource = ScheduleConflictResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

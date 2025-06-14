<?php

namespace App\Filament\Resources\TeacherHourCounterResource\Pages;

use App\Filament\Resources\TeacherHourCounterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherHourCounters extends ListRecords
{
    protected static string $resource = TeacherHourCounterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

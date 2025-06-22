<?php

namespace App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Filament\Resources\ScheduleConflictResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleConflict extends EditRecord
{
    protected static string $resource = ScheduleConflictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

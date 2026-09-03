<?php

namespace App\Filament\Resources\TeacherHourCounterResource\Pages;

use App\Filament\Resources\TeacherHourCounterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherHourCounter extends EditRecord
{
    protected static string $resource = TeacherHourCounterResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\WeekdayResource\Pages;

use App\Filament\Resources\WeekdayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeekday extends EditRecord
{
    protected static string $resource = WeekdayResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

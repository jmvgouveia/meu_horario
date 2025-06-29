<?php

namespace App\Filament\Resources\WeekdayResource\Pages;

use App\Filament\Resources\WeekdayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeekdays extends ListRecords
{
    protected static string $resource = WeekdayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

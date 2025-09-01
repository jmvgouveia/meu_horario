<?php

namespace App\Filament\Resources\RoomBlockedHoursResource\Pages;

use App\Filament\Resources\RoomBlockedHoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomBlockedHours extends ListRecords
{
    protected static string $resource = RoomBlockedHoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

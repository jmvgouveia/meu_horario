<?php

namespace App\Filament\Resources\RoomBlockedHoursResource\Pages;

use App\Filament\Resources\RoomBlockedHoursResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomBlockedHours extends EditRecord
{
    protected static string $resource = RoomBlockedHoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

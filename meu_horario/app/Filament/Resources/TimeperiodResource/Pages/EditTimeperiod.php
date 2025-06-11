<?php

namespace App\Filament\Resources\TimeperiodResource\Pages;

use App\Filament\Resources\TimeperiodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeperiod extends EditRecord
{
    protected static string $resource = TimeperiodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

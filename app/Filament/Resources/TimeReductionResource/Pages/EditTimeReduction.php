<?php

namespace App\Filament\Resources\TimeReductionResource\Pages;

use App\Filament\Resources\TimeReductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeReduction extends EditRecord
{
    protected static string $resource = TimeReductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

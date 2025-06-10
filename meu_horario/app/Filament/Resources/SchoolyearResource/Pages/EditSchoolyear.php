<?php

namespace App\Filament\Resources\SchoolyearResource\Pages;

use App\Filament\Resources\SchoolyearResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolyear extends EditRecord
{
    protected static string $resource = SchoolyearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

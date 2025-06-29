<?php

namespace App\Filament\Resources\NationalityResource\Pages;

use App\Filament\Resources\NationalityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNationality extends EditRecord
{
    protected static string $resource = NationalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

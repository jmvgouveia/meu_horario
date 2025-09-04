<?php

namespace App\Filament\Resources\RegistrationSubjectResource\Pages;

use App\Filament\Resources\RegistrationSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationSubject extends EditRecord
{
    protected static string $resource = RegistrationSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

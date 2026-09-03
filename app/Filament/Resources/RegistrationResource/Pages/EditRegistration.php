<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['id_schoolyear'] = RegistrationResource::activeSchoolYearId();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_schoolyear'] = RegistrationResource::activeSchoolYearId();

        return $data;
    }
}

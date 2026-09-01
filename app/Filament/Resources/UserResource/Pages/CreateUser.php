<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserActivationService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Str::random(40);
        $data['is_active'] = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(UserActivationService::class)->issueAndNotify($this->record);
    }
}

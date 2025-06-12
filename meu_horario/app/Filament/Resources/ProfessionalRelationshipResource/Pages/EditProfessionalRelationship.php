<?php

namespace App\Filament\Resources\ProfessionalRelationshipResource\Pages;

use App\Filament\Resources\ProfessionalRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfessionalRelationship extends EditRecord
{
    protected static string $resource = ProfessionalRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ProfessionalRelationshipResource\Pages;

use App\Filament\Resources\ProfessionalRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfessionalRelationships extends ListRecords
{
    protected static string $resource = ProfessionalRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

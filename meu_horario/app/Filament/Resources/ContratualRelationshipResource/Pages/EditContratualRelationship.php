<?php

namespace App\Filament\Resources\ContratualRelationshipResource\Pages;

use App\Filament\Resources\ContratualRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContratualRelationship extends EditRecord
{
    protected static string $resource = ContratualRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

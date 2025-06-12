<?php

namespace App\Filament\Resources\ContratualRelationshipResource\Pages;

use App\Filament\Resources\ContratualRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContratualRelationships extends ListRecords
{
    protected static string $resource = ContratualRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

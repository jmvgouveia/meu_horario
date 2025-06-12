<?php

namespace App\Filament\Resources\TimeReductionResource\Pages;

use App\Filament\Resources\TimeReductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeReductions extends ListRecords
{
    protected static string $resource = TimeReductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

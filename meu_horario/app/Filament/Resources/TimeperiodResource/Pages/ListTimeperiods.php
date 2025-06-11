<?php

namespace App\Filament\Resources\TimeperiodResource\Pages;

use App\Filament\Resources\TimeperiodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeperiods extends ListRecords
{
    protected static string $resource = TimeperiodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}

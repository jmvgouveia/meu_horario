<?php

namespace App\Filament\Resources\SalaryScaleResource\Pages;

use App\Filament\Resources\SalaryScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaryScale extends EditRecord
{
    protected static string $resource = SalaryScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

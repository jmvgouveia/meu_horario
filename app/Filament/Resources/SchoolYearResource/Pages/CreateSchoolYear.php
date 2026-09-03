<?php

namespace App\Filament\Resources\SchoolYearResource\Pages;

use App\Filament\Resources\SchoolYearResource;
use App\Models\SchoolYear;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSchoolYear extends CreateRecord
{
    protected static string $resource = SchoolYearResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function handleRecordCreation(array $data): Model
    {
        return SchoolYear::createWithExclusiveActive($data);
    }
}

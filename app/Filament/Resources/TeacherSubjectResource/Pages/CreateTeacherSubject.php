<?php

namespace App\Filament\Resources\TeacherSubjectResource\Pages;

use App\Filament\Resources\TeacherSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherSubject extends CreateRecord
{
    protected static string $resource = TeacherSubjectResource::class;

    use \App\Filament\Resources\Concerns\RedirectsToList;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_schoolyear'] = TeacherSubjectResource::activeSchoolYearId();

        return $data;
    }
}

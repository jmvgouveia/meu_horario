<?php

namespace App\Filament\Resources\TeacherSubjectResource\Pages;

use App\Filament\Resources\TeacherSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherSubject extends EditRecord
{
    protected static string $resource = TeacherSubjectResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['id_schoolyear'] = TeacherSubjectResource::activeSchoolYearId();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CourseSubjectResource\Pages;

use App\Filament\Resources\CourseSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSubject extends EditRecord
{
    protected static string $resource = CourseSubjectResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['id_schoolyear'] = CourseSubjectResource::activeSchoolYearId();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

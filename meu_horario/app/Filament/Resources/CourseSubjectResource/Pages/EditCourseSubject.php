<?php

namespace App\Filament\Resources\CourseSubjectResource\Pages;

use App\Filament\Resources\CourseSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSubject extends EditRecord
{
    protected static string $resource = CourseSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

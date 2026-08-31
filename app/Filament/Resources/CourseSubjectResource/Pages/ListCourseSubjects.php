<?php

namespace App\Filament\Resources\CourseSubjectResource\Pages;

use App\Filament\Resources\CourseSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseSubjects extends ListRecords
{
    use \App\Filament\Concerns\HandlesSchoolYearHistory;

    protected static string $resource = CourseSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => ! $this->isHistoricalMode()),
        ];
    }
}

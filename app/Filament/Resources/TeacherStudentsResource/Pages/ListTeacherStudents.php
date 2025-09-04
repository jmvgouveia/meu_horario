<?php

namespace App\Filament\Resources\TeacherStudentsResource\Pages;

use App\Filament\Resources\TeacherStudentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherStudents extends ListRecords
{
    protected static string $resource = TeacherStudentsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // Se for professor, nega acesso à listagem
        if ($user->hasRole('Professor')) {
            abort(403);
        }
    }
}

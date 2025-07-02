<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Department;
use App\Models\Room;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewAP extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [

            Stat::make('Número de Cursos', Course::count())
                ->description('Número total de Cursos')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Número de Turmas', Classes::count())
                ->description('Número total de Turmas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Número de Alunos', Student::count())
                ->description('Número total de Alunos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof \App\Models\User
            && $user->hasAnyRole(['Super Admin', 'Área Pedagógica']);
    }
}

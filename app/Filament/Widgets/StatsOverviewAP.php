<?php

namespace App\Filament\Widgets;

use App\Models\Classes;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
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
                ->icon('heroicon-m-academic-cap')
                ->extraAttributes(['class' => 'maestro-stat-card']),

            Stat::make('Número de Turmas', Classes::count())
                ->description('Número total de Turmas')
                ->icon('heroicon-m-user-group')
                ->extraAttributes(['class' => 'maestro-stat-card']),

            Stat::make('Número de Alunos', Student::count())
                ->description('Número total de Alunos')
                ->icon('heroicon-m-user-group')
                ->extraAttributes(['class' => 'maestro-stat-card']),

        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            && $user->hasAnyRole(['Super Admin', 'Área Pedagógica']);
    }
}

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

class StatsOverviewRH extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [

            Stat::make('Número de Docentes', Teacher::count())
                ->description('Número total de docentes')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger'),


            Stat::make('Número de Deparmentos', Department::count())
                ->description('Número total de Departamentos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),


        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof \App\Models\User
            && $user->hasAnyRole(['Super Admin', 'Recursos Humanos']);
    }
}

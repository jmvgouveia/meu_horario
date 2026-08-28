<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
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
                ->icon('heroicon-m-user-group')
                ->extraAttributes(['class' => 'maestro-stat-card']),

            Stat::make('Número de Deparmentos', Department::count())
                ->description('Número total de Departamentos')
                ->icon('heroicon-m-user-group')
                ->extraAttributes(['class' => 'maestro-stat-card']),
        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            && $user->hasAnyRole(['Super Admin', 'Recursos Humanos']);
    }
}

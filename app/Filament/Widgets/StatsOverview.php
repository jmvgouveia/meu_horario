<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Edifícios', Building::count())
                ->description('Número total de edifícios')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Total de Salas', Room::count())
                ->description('Número total de salas')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Média de Salas', number_format(Room::count() / max(Building::count(), 1), 1))
                ->description('Média de salas por edifício')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Super Admin');
    }
}

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
                ->description('Edifícios disponíveis')
                ->icon('heroicon-m-building-office')
                ->extraAttributes(['class' => 'maestro-stat-card']),

            Stat::make('Total de Salas', Room::count())
                ->description('Salas disponíveis')
                ->icon('heroicon-m-building-office-2')
                ->extraAttributes(['class' => 'maestro-stat-card']),

            Stat::make('Média de Salas por Edifício', number_format(Room::count() / max(Building::count(), 1), 1))
                ->description('Média de salas por edifício')
                ->icon('heroicon-m-calculator')
                ->extraAttributes(['class' => 'maestro-stat-card']),
        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Super Admin');
    }
}

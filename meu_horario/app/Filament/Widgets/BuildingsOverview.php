<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class BuildingsOverview extends ChartWidget
{
    protected static ?string $heading = 'Edifícios e Salas';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $buildings = Building::withCount('rooms')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Número de Salas',
                    'data' => $buildings->pluck('rooms_count')->toArray(),
                    'backgroundColor' => [
                        '#2563EB',
                        '#16A34A',
                        '#DC2626',
                        '#CA8A04',
                        '#9333EA',
                        '#DB2777',
                        '#2563EB',
                        '#16A34A',
                    ],
                ],
            ],
            'labels' => $buildings->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof \App\Models\User && $user->hasRole('Super Admin');
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class BuildingsOverview extends ChartWidget
{
    protected static ?string $heading = 'Edifícios e Salas';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '210px';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $buildings = Building::withCount('rooms')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Número de Salas',
                    'data' => $buildings->pluck('rooms_count')->toArray(),
                    'backgroundColor' => [
                        '#063B82',
                        '#1558A6',
                        '#2E6BB5',
                        '#5C8FC8',
                        '#90B4DA',
                        '#C1D7EA',
                        '#FFBF00',
                        '#D9A300',
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $buildings->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                    'grid' => ['display' => false],
                    'ticks' => ['display' => false],
                ],
                'y' => [
                    'display' => false,
                    'grid' => ['display' => false],
                    'ticks' => ['display' => false],
                ],
            ],
            'cutout' => '68%',
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Super Admin');
    }
}

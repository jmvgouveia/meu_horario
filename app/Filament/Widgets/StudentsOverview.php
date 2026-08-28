<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class StudentsOverview extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Alunos por Sexo';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '210px';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {

        $students = Student::selectRaw('genders.gender, COUNT(*) as total')
            ->join('genders', 'students.id_gender', '=', 'genders.id')
            ->groupBy('genders.gender')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Número de Alunos',
                    'data' => $students->pluck('total')->toArray(),
                    'backgroundColor' => ['#063B82', '#FFBF00', '#5C8FC8', '#90B4DA'],
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $students->pluck('gender')->toArray(),
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
                'maestroCenterText' => [
                    'display' => true,
                    'label' => 'Alunos',
                ],
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

        return $user instanceof User
            && $user->hasAnyRole(['Super Admin', 'Área Pedagógica']);
    }
}

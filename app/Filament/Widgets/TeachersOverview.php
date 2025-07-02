<?php

namespace App\Filament\Widgets;

use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class TeachersOverview extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Professores por Sexo';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $docentes = Teacher::selectRaw('genders.gender, COUNT(*) as total')
            ->join('genders', 'teachers.id_gender', '=', 'genders.id')
            ->groupBy('genders.gender')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Número de Professores',
                    'data' => $docentes->pluck('total')->toArray(),
                    'backgroundColor' => ['#2563EB', '#DC2626'],
                ],
            ],
            'labels' => $docentes->pluck('gender')->toArray(),
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

        return $user instanceof User
            && $user->hasAnyRole(['Super Admin', 'Recursos Humanos']);
    }
}

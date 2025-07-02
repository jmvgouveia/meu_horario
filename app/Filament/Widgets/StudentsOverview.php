<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class StudentsOverview extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Alunos por Sexo';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Buscar a distribuição de students por gênero
        $students = Student::selectRaw('genders.gender, COUNT(*) as total')
            ->join('genders', 'students.id_gender', '=', 'genders.id')  // Junção com a tabela 'genders'
            ->groupBy('genders.gender')  // Agrupar por gênero
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Número de Alunos',
                    'data' => $students->pluck('total')->toArray(),
                    'backgroundColor' => ['#e864ba', '#2563EB'], // Azul e Vermelho (exemplo)
                ],
            ],
            'labels' => $students->pluck('gender')->toArray(), // Labels com os nomes dos gêneros
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

        return $user instanceof \App\Models\User
            && $user->hasAnyRole(['Super Admin', 'Área Pedagógica']);
    }
}

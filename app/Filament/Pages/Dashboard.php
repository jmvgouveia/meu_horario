<?php

namespace App\Filament\Pages;

use App\Models\SchoolYear;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Visão Geral';

    public function getSubheading(): ?string
    {
        return 'Acompanhe os principais indicadores do Conservatório.';
    }

    protected function getHeaderActions(): array
    {
        $activeSchoolYear = SchoolYear::query()
            ->where('active', true)
            ->value('schoolyear');

        if (! $activeSchoolYear) {
            return [];
        }

        return [
            Action::make('activeSchoolYear')
                ->label("Ano letivo {$activeSchoolYear}")
                ->icon('heroicon-m-calendar-days')
                ->disabled()
                ->outlined()
                ->extraAttributes(['class' => 'maestro-school-year']),
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Visão Geral';

    public function getSubheading(): ?string
    {
        return 'Bem-vindo à Plataforma Integrada de Gestão do Conservatório.';
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }
}

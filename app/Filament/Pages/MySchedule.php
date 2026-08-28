<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\WeeklyScheduleWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class MySchedule extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Horários';

    protected static ?string $navigationLabel = 'O Meu Horário';

    protected static ?string $title = 'O Meu Horário';

    protected static ?string $slug = 'o-meu-horario';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.my-schedule';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Professor');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WeeklyScheduleWidget::class,
        ];
    }
}

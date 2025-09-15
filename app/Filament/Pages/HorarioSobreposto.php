<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Teacher;
use App\Services\MergedScheduleCalendarService;

class HorarioSobreposto extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Horário (sobreposto)';
    protected static ?string $title           = 'Horário sobreposto de docentes';
    protected static string $view             = 'filament.pages.horario-sobreposto';

    // Estado do formulário
    public array $data = [
        'teacher_ids' => [],
    ];

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        // Oculta da navegação apenas se for professor
        if ($user && $user->hasRole('Super Admin')) {
            return true;
        }

        return false; // visível para admins, superadmins, etc.
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Selecionar docentes')
                ->schema([
                    Forms\Components\MultiSelect::make('teacher_ids')
                        ->label('Docentes')
                        ->options(fn() => Teacher::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->reactive(),
                ])->columns(1),
        ])->statePath('data');
    }

    // Propriedade computada Livewire: $this->merged
    public function getMergedProperty(): ?array
    {
        $ids = $this->data['teacher_ids'] ?? [];
        if (empty($ids)) {
            return null;
        }

        return MergedScheduleCalendarService::buildForTeachers($ids);
    }
}

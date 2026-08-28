<?php

namespace App\Filament\Pages;

use App\Models\Teacher;
use App\Models\User;
use App\Models\SchoolYear;
use App\Services\MergedScheduleCalendarService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class HorarioSobreposto extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Coordenação de Departamento';
    protected static ?string $navigationLabel = 'Horário Departamento';
    protected static ?string $title           = 'Horário sobreposto de docentes';
    protected static string $view             = 'filament.pages.horario-sobreposto';

    // Estado do formulário
    public array $data = [
        'teacher_ids' => [],
    ];

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            && (
                $user->hasRole('Super Admin')
                || static::userHasCoordinatorPosition($user)
            );
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Selecionar docentes')
                ->schema([
                    Forms\Components\MultiSelect::make('teacher_ids')
                        ->label('Docentes')
                        ->options(fn() => $this->teacherOptions())
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
        $ids = array_values(array_intersect(
            array_map('intval', $ids),
            $this->allowedTeacherIds(),
        ));

        if (empty($ids)) {
            return null;
        }

        return MergedScheduleCalendarService::buildForTeachers($ids);
    }

    protected function teacherOptions(): array
    {
        return $this->allowedTeacherQuery()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function allowedTeacherIds(): array
    {
        return $this->allowedTeacherQuery()
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    protected function allowedTeacherQuery()
    {
        $user = Filament::auth()->user();
        $activeSchoolYearId = SchoolYear::query()->where('active', true)->value('id');
        $query = Teacher::query()
            ->when(
                $activeSchoolYearId,
                fn($query) => $query->whereHas(
                    'schedules',
                    fn($scheduleQuery) => $scheduleQuery
                        ->where('id_schoolyear', $activeSchoolYearId)
                        ->whereIn('status', ['Aprovado', 'Aprovado DP'])
                ),
                fn($query) => $query->whereRaw('0 = 1')
            );

        if ($user instanceof User && $user->hasRole('Super Admin')) {
            return $query;
        }

        return $query->where('id_department', $user?->teacher?->id_department);
    }

    protected static function userHasCoordinatorPosition(User $user): bool
    {
        $activeSchoolYearId = SchoolYear::query()->where('active', true)->value('id');
        $teacher = $user->teacher;

        if (! $activeSchoolYearId || ! $teacher?->id_department) {
            return false;
        }

        return $teacher->positions()
            ->wherePivot('id_schoolyear', $activeSchoolYearId)
            ->whereIn('positions.name', static::coordinatorPositionNames())
            ->exists();
    }

    protected static function coordinatorPositionNames(): array
    {
        return [
            'Coordenador de Departamento',
            'Coordenador Departamento Curricular (10)',
            'Coordenador Departamento Curricular (20)',
            'Coordenador Departamento Curricular (30)',
            'Coordenador Departamento Curricular (+31)',
        ];
    }
}

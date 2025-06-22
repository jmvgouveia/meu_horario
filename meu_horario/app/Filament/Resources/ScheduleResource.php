<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Building;
use App\Models\Classes;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timeperiod;
use App\Models\Weekday;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Actions as ActionGroup;
use Filament\Forms\Components\Actions\Action;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Marcações';
    protected static ?string $navigationIcon = 'heroicon-s-calendar-date-range';
    protected static ?int $navigationSort = 1;
    public ?Schedule $conflictingSchedule = null; // Marcação que gerou conflito

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dia / Hora')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_weekday')
                                    ->label('Dia da Semana')
                                    ->required()
                                    ->options(Weekday::all()->pluck('weekday', 'id'))
                                    ->placeholder('Selecione o dia da semana'),
                                Select::make('id_timeperiod')
                                    ->label('Hora de Início')
                                    ->required()
                                    ->placeholder('Selecione a hora de início')
                                    ->options(Timeperiod::all()->pluck('description', 'id'))
                                    ->reactive(),
                            ]),
                    ]),
                Section::make('Local da Aula')
                    ->description('Selecione o núcleo/pólo e a sala onde será dada a aula')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_building')
                                    ->label('Núcleo ou Pólo')
                                    ->required()
                                    ->options(Building::all()->pluck('name', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('id_room', null))
                                    ->placeholder('Selecione o local da aula')
                                    ->afterStateHydrated(function (callable $set, ?Schedule $record) {
                                        if ($record && $record->id_room && $record->room) {
                                            $set('id_building', $record->room->building_id);
                                        }
                                    }),
                                Select::make('id_room')
                                    ->label('Sala')
                                    ->required()
                                    ->options(function (callable $get, ?Schedule $record) {
                                        $buildingId = $get('id_building') ?? $record?->room?->building_id;

                                        if (!$buildingId) return [];

                                        return \App\Models\Room::where('id_building', $buildingId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->placeholder('Selecione a sala')
                                    ->reactive()
                                    ->afterStateHydrated(function (callable $set, ?Schedule $record) {
                                        if ($record && $record->id_room) {
                                            $set('id_room', $record->id_room);
                                        }
                                    }),
                            ]),
                        ]),
                Section::make('Composição da Aula')
                    ->description('Defina a disciplina, turmas e alunos envolvidos')
                    ->schema([
                        Select::make('id_subject')
                            ->label('Disciplina')
                            ->required()
                            ->reactive()
                            ->options(function () {
                                $userId = Auth::id();
                                $teacher = Teacher::where('id_user', $userId)->first();
                                if (!$teacher) return collect(['' => 'Este utilizador não é um professor']);
                                $activeYear = SchoolYear::where('active', true)->first();
                                if (!$activeYear) return collect(['' => 'Nenhum ano letivo ativo']);
                                $subjects = Subject::whereHas('teachers', function ($query) use ($teacher, $activeYear) {
                                    $query->where('id_teacher', $teacher->id)
                                        ->where('teacher_subjects.id_schoolyear', $activeYear->id);
                                })->pluck('name', 'id');
                                return $subjects->isEmpty()
                                    ? collect(['' => 'Nenhuma disciplina atribuída neste ano letivo'])
                                    : $subjects;
                            })
                            ->placeholder('Escolha a disciplina')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('id_subject', $state);
                                $set('id_classes', []);
                                $set('alunos', []);
                            }),

                        Select::make('id_classes')
                            ->label('Turmas')
                            ->multiple()
                            ->required(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $subjectName = Subject::find($subjectId)?->subject;

                                return !in_array(strtolower($subjectName), ['reunião', 'tee']);
                            })
                            ->helperText('Selecione a(s) turma(s) que vão assistir à aula')
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, ?Schedule $record) {
                                $set('id_classes', $record?->classes()->pluck('classes.id')->toArray());
                            })
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $buildingId = $get('id_building');

                                if (!$subjectId || !$buildingId) {
                                    return [];
                                }

                                $subject = Subject::find($subjectId);
                                if (!$subject) {
                                    return [];
                                }

                                // Cursos associados à disciplina
                                $courseIds = $subject->courses()->pluck('courses.id');

                                // Turmas associadas ao curso e ao edifício
                                return Classes::whereIn('id_course', $courseIds)
                                    ->where('id_building', $buildingId)
                                    ->pluck('name', 'id');
                            }),
                        Toggle::make('filtrar_por_turma')
                            ->label('Filtrar alunos pelas turmas selecionadas')
                            ->default(true)
                            ->reactive(),
                        CheckboxList::make('students')
                            ->label('Alunos matriculados na disciplina')
                            ->helperText('Selecione os alunos que vão assistir à aula')
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, ?Schedule $record) {
                                if ($record && $record->exists) {
                                    $studentIds = $record->students()->pluck('students.id')->filter()->values()->toArray();

                                    if (!empty($studentIds)) {
                                        $set('students', $studentIds);
                                    } else {
                                        $set('students', []);
                                    }
                                } else {
                                    $set('students', []);
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $studentIds = is_array($state) ? $state : [];

                                if (count($studentIds) > 0) {
                                    // Define o turno sugerido
                                    $numeros = Student::whereIn('id', $studentIds)
                                        ->pluck('number')
                                        ->sort()
                                        ->implode(', ');
                                    $set('turno', $numeros);

                                    // Se não estiver a filtrar por turma, atualiza as turmas com base nos alunos
                                    if (!$get('filtrar_por_turma')) {
                                        $classIds = Registration::whereIn('id_student', $studentIds)
                                            ->pluck('id_class')
                                            ->unique()
                                            ->filter()
                                            ->values()
                                            ->toArray();

                                        $set('id_classes', $classIds);
                                    }
                                } else {
                                    $set('turno', null);
                                }
                            })
                            ->columns(4)
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $schoolYear = SchoolYear::where('active', true)->first();
                                $classIds = $get('id_classes') ?? [];
                                $filtrarPorTurma = $get('filtrar_por_turma');

                                if (!$subjectId || !$schoolYear) return [];

                                $registrationIds = DB::table('registrations_subjects')
                                    ->where('id_subject', $subjectId)
                                    ->pluck('id_registration');

                                if ($registrationIds->isEmpty()) return [];

                                $query = Registration::with(['student', 'class'])
                                    ->whereIn('id', $registrationIds)
                                    ->where('id_schoolyear', $schoolYear->id);

                                if ($filtrarPorTurma && !empty($classIds)) {
                                    $query->whereIn('id_class', $classIds);
                                }

                                return $query->get()->mapWithKeys(function ($registration) {
                                    $student = $registration->student;
                                    $turma = $registration->class?->name ?? '—';
                                    if (!$student) return [];

                                    return [
                                        $registration->id_student => "{$student->number} - {$student->name} - {$turma}",
                                    ];
                                });
                            }),
                        Section::make('Turno')
                            ->description('Indique o turno da aula')
                            ->schema([
                                // Campo mostrado quando NÃO há alunos selecionados
                                Select::make('shift') //TODO: alterar campo na tabela
                                    ->label('Turno')
                                    ->visible(function (callable $get) {
                                        $students = $get('students');
                                        return is_array($students) ? count($students) === 0 : true; // mostra se for array vazio ou não for array
                                    })
                                    ->options(function () {
                                        $acronym = \Illuminate\Support\Facades\Auth::user()?->teacher?->acronym ?? '';
                                        return [
                                            "Turno A - $acronym" => "Turno A - $acronym",
                                            "Turno B - $acronym" => "Turno B - $acronym",
                                            "Turno C - $acronym" => "Turno C - $acronym",
                                            "Turno D - $acronym" => "Turno D - $acronym",
                                        ];
                                    })
                                    ->placeholder('Em caso de ser a turma toda, selecione o turno'),

                                // Campo visível apenas quando há alunos selecionados
                                TextInput::make('shift')
                                    ->label('Turno Gerado (automático)')
                                    ->visible(function (callable $get) {
                                        $students = $get('students');
                                        return is_array($students) && count($students) > 0;
                                    })
                                    ->extraAttributes(['readonly' => true])
                                    ->default(fn(callable $get, ?Schedule $record) => $get('shift') ?? $record?->shift)
                                    ->placeholder('Será preenchido automaticamente com os números dos alunos'),
                            ]),

                    ]),

                ActionGroup::make([
                    Action::make('justificarConflito')
                        ->label('Solicitar Troca de Horário')
                        ->visible(fn($livewire) => $livewire->conflictingSchedule !== null)
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->modalHeading('Pedido de Troca de Horário')
                        ->modalWidth('xl')
                        ->modalDescription('Por favor, forneça uma justificação para a troca de horário.')
                        ->modalSubmitActionLabel('Submeter Justificação')
                        ->modalCancelActionLabel('Cancelar')
                        ->form([

                            Textarea::make('justification')
                                ->label('Escreva a justificação')
                                ->required()
                                ->minLength(10),
                        ])
                        ->action(fn(array $data, $livewire) => $livewire->submitJustification($data)),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    // Filtrar os horários para mostrar apenas os do professor autenticado
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Verifica se o utilizador está autenticado e é um professor com registo
        if (Auth::check() && Auth::user()->teacher) {
            $teacherId = Auth::user()->teacher->id;

            // Filtra os horários para mostrar apenas os do professor autenticado
            $query->where('id_teacher', $teacherId);
        }

        return $query;
    }
}

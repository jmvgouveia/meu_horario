<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Building;
use App\Models\Classes;
use App\Models\Registration;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Timeperiod;
use App\Models\Weekday;
use App\Models\Teacher;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeacherHourCounter;
use Dom\Text;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions as ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class ScheduleResource extends Resource
{

    protected static ?string $model = Schedule::class;
    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Marcação de Horários';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public ?Schedule $conflictingSchedule = null;


    public static function getLabel(): string
    {
        return 'Marcação de Horários';
    }

    public static function getPluralLabel(): string
    {
        return 'Marcações de Horários';
    }
    public static function exportSchedules(?Collection $records = null): StreamedResponse
    {
        $anoLetivoAtivoId = \App\Models\SchoolYear::where('active', true)->value('id');

        if ($records) {
            // Aplica filtro de status e ano letivo diretamente no collection
            $schedules = $records->load([
                'teacher',
                'room',
                'subject',
                'weekday',
                'timePeriod',
                'classes',
                'students'
            ])->filter(function ($item) use ($anoLetivoAtivoId) {
                return in_array($item->status, ['Aprovado', 'Aprovado DP'])
                    && $item->id_schoolyear === $anoLetivoAtivoId;
            });
        } else {
            // Filtro direto via query
            $schedules = Schedule::query()
                ->whereIn('status', ['Aprovado', 'Aprovado DP'])
                ->where('id_schoolyear', $anoLetivoAtivoId)
                ->with(['teacher', 'room', 'subject', 'weekday', 'timePeriod', 'classes', 'students'])
                ->get();
        }

        $now = now()->format('Y-m-d_H-i');
        $filename = "horarios-{$now}.txt";

        return response()->streamDownload(function () use ($schedules) {
            $handle = fopen('php://output', 'w');

            foreach ($schedules as $schedule) {
                $turmaAlunos = [];

                if ($schedule->students->isNotEmpty()) {
                    foreach ($schedule->students as $student) {
                        $registration = Registration::where('id_student', $student->id)
                            ->where('id_schoolyear', $schedule->id_schoolyear)
                            ->whereIn('id_class', $schedule->classes->pluck('id'))
                            ->with('class')
                            ->first();

                        if ($registration && $registration->class) {
                            $turmaNome = $registration->class->name;
                            $turmaAno = $registration->class->year;

                            $turmaAlunos[$turmaNome]['ano'] = $turmaAno;
                            $turmaAlunos[$turmaNome]['alunos'][] = "{$student->number}";
                        }
                    }
                } else {
                    foreach ($schedule->classes as $class) {
                        $linha = [
                            $schedule->id_weekday + 2,
                            $schedule->id_timeperiod,
                            "\"{$class->name}\"",
                            $class->year,
                            "\"{$schedule->teacher->number}\"",
                            "\"{$schedule->subject->acronym}\"",
                            "\"{$schedule->room->name}\"",
                            "\"\"",
                        ];

                        fputs($handle, implode('|', $linha) . "\n");
                    }

                    continue;
                }

                foreach ($turmaAlunos as $turma => $info) {
                    $linha = [
                        $schedule->id_weekday + 2,
                        $schedule->id_timeperiod,
                        "\"$turma\"",
                        $info['ano'],
                        "\"{$schedule->teacher->number}\"",
                        "\"{$schedule->subject->acronym}\"",
                        "\"{$schedule->room->name}\"",
                        "\"" . implode(',', $info['alunos']) . "\"",
                    ];

                    fputs($handle, implode('|', $linha) . "\n");
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }



    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Verifica se está autenticado e tem professor associado
        if (Auth::check() && Auth::user()->teacher) {
            $teacherId = Auth::user()->teacher->id;

            // Filtra pelo professor
            $query->where('id_teacher', $teacherId);
        }

        // Obtém o ano letivo ativo (ajusta conforme o teu modelo)
        $anoLetivoAtivo = \App\Models\SchoolYear::where('active', true)->first();

        if ($anoLetivoAtivo) {
            $query->where('id_schoolyear', $anoLetivoAtivo->id);
        } else {
            // Se não houver ano letivo ativo, retorna vazio para segurança
            $query->whereRaw('0 = 1');
        }

        return $query;
    }


    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Section::make('Dia / Hora')
                    ->collapsible()
                    ->description('Informe quando a aula será realizada')
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
                    ->collapsible()
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
                                            $set('id_building', $record->room->id_building);
                                        }
                                    }),

                                Select::make('id_room')
                                    ->label('Sala')
                                    ->required()
                                    //   ->live()
                                    ->disabled(fn(callable $get) => blank($get('id_building')))
                                    //->hint('Preencha o campo anterior primeiro)')
                                    ->placeholder('Tem que preencher o Núcleo/Pólo')
                                    //->hint('Tem que preencher o Núcleo/Pólo primeiro')
                                    ->options(function (callable $get, ?Schedule $record) {
                                        $buildingId = $get('id_building') ?? $record?->room?->id_building;

                                        if (!$buildingId) return [];

                                        return Room::where('id_building', $buildingId)->pluck('name', 'id');
                                    })
                                    ->searchable()

                                    ->reactive()
                                    ->afterStateHydrated(function (callable $set, ?Schedule $record) {
                                        if ($record && $record->id_room) {
                                            $set('id_room', $record->id_room);
                                        }
                                    }),
                            ]),
                    ]),


                Section::make('Composição da Aula')
                    ->collapsible()
                    ->description('Defina a disciplina, turmas e alunos envolvidos')
                    ->schema([
                        Select::make('id_subject')
                            ->label('Disciplina')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->disabled(fn(callable $get) => blank($get('id_room')))
                            //  ->hint('Tem que preencher a Sala primeiro')
                            ->placeholder('Tem que preencher a Sala')
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

                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('id_subject', $state);
                                $set('id_classes', []);
                                $set('alunos', []);
                            }),

                        Select::make('id_classes')
                            ->label('Turmas')
                            ->disabled(fn(callable $get) => blank($get('id_subject')))
                            //->hint('Tem que preencher a disciplina primeiro')
                            ->placeholder('Tem que preencher a disciplina primeiro')
                            ->multiple()
                            ->required(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $subjectName = Subject::find($subjectId)?->name;
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

                                $courseIds = $subject->courses()->pluck('courses.id');

                                return Classes::whereIn('id_course', $courseIds)
                                    ->where('id_building', $buildingId)
                                    ->pluck('name', 'id');
                            }),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('filtrar_por_turma')
                                    ->label('Filtrar alunos pelas turmas selecionadas')
                                    ->default(true)
                                    ->reactive(),

                                Toggle::make('filter_last_year_students')
                                    ->label('Mostrar os meus alunos (último ano letivo)')
                                    ->default(true)
                                    ->reactive(),
                            ]),
                        TextInput::make('filter_student_name')
                            ->label('Filtrar por nome do aluno')
                            ->placeholder('Digite parte do nome...')
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
                                    $numeros = Student::whereIn('id', $studentIds)
                                        ->pluck('number')
                                        ->sort()
                                        ->implode(', ');

                                    $set('shift', $numeros);

                                    if (!$get('filtrar_por_turma')) {
                                        $schoolYear = SchoolYear::where('active', true)->first();
                                        $classIds = Registration::whereIn('id_student', $studentIds)
                                            ->where('id_schoolyear', $schoolYear->id)
                                            ->pluck('id_class')
                                            ->unique()
                                            ->filter()
                                            ->values()
                                            ->toArray();

                                        $set('id_classes', $classIds);
                                    }
                                } else {
                                    $set('shift', null);
                                }
                            })
                            ->columns(4)
                            ->options(function (callable $get) {
                                $subjectId = $get('id_subject');
                                $schoolYear = SchoolYear::where('active', true)->first();
                                $classIds = $get('id_classes') ?? [];
                                $filtrarPorTurma = $get('filtrar_por_turma');
                                $filtrarUltimoAno = $get('filter_last_year_students');
                                $filtroNome = trim($get('filter_student_name'));

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

                                if ($filtrarUltimoAno) {
                                    $professorId = Auth::user()?->teacher?->id;
                                    $anoAnterior = $schoolYear->id - 1;
                                    $anoLetivoAnterior = \App\Models\SchoolYear::find($anoAnterior);

                                    if (!$anoLetivoAnterior) return [];

                                    $scheduleIds = DB::table('schedules')
                                        ->where('id_teacher', $professorId)
                                        ->where('id_subject', $subjectId)
                                        ->where('id_schoolyear', $anoLetivoAnterior->id)
                                        ->pluck('id');

                                    $studentIdsPermitidos = DB::table('schedules_students')
                                        ->whereIn('id_schedule', $scheduleIds)
                                        ->pluck('id_student');

                                    if ($studentIdsPermitidos->isNotEmpty()) {
                                        $query->whereIn('id_student', $studentIdsPermitidos);
                                    } else {
                                        return [];
                                    }
                                }

                                // 🔍 Aplica filtro por nome (se preenchido)
                                if (!empty($filtroNome)) {
                                    $query->whereHas('student', function ($q) use ($filtroNome) {
                                        $q->where('name', 'like', '%' . $filtroNome . '%');
                                    });
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
                        // ->options(function (callable $get) {
                        //     $subjectId = $get('id_subject');
                        //     $schoolYear = SchoolYear::where('active', true)->first();
                        //     $classIds = $get('id_classes') ?? [];
                        //     $filtrarPorTurma = $get('filtrar_por_turma');
                        //     $filtrarUltimoAno = $get('filter_last_year_students');

                        //     if (!$subjectId || !$schoolYear) return [];

                        //     $registrationIds = DB::table('registrations_subjects')
                        //         ->where('id_subject', $subjectId)
                        //         ->pluck('id_registration');

                        //     if ($registrationIds->isEmpty()) return [];

                        //     $query = Registration::with(['student', 'class'])
                        //         ->whereIn('id', $registrationIds)
                        //         ->where('id_schoolyear', $schoolYear->id);

                        //     if ($filtrarPorTurma && !empty($classIds)) {
                        //         $query->whereIn('id_class', $classIds);
                        //     }

                        //     if ($filtrarUltimoAno) {
                        //         $professorId = Auth::user()?->teacher?->id;

                        //         // Procurar ano letivo anterior com base no campo 'ano'
                        //         $anoAnterior = $schoolYear->id - 1;
                        //         $anoLetivoAnterior = \App\Models\SchoolYear::where('id', $anoAnterior)->first();

                        //         // Se não existir ano letivo anterior, não filtra nada
                        //         if (!$anoLetivoAnterior) {
                        //             return [];
                        //         }

                        //         // Horários do professor no ano letivo anterior
                        //         $scheduleIds = DB::table('schedules')
                        //             ->where('id_teacher', $professorId)
                        //             ->where('id_subject', $subjectId)
                        //             ->where('id_schoolyear', $anoLetivoAnterior->id)
                        //             ->pluck('id');

                        //         // Alunos que estiveram nesses horários
                        //         $studentIdsPermitidos = DB::table('schedules_students')
                        //             ->whereIn('id_schedule', $scheduleIds)
                        //             ->pluck('id_student');

                        //         if ($studentIdsPermitidos->isNotEmpty()) {
                        //             $query->whereIn('id_student', $studentIdsPermitidos);
                        //         } else {
                        //             return [];
                        //         }
                        //     }


                        //     return $query->get()->mapWithKeys(function ($registration) {
                        //         $student = $registration->student;
                        //         $turma = $registration->class?->name ?? '—';
                        //         if (!$student) return [];

                        //         return [
                        //             $registration->id_student => "{$student->number} - {$student->name} - {$turma}",
                        //         ];
                        //     });
                        // }),

                        Section::make('Turno')
                            ->collapsible()
                            ->description('Indique o turno da aula')
                            ->schema([
                                Select::make('shift')
                                    ->label('Turno')
                                    ->visible(function (callable $get) {
                                        $students = $get('students');
                                        return is_array($students) ? count($students) === 0 : true;
                                    })
                                    ->options(function () {
                                        $acronym = Auth::user()?->teacher?->acronym ?? '';
                                        return [
                                            "Turno A - $acronym" => "Turno A - $acronym",
                                            "Turno B - $acronym" => "Turno B - $acronym",
                                            "Turno C - $acronym" => "Turno C - $acronym",
                                            "Turno D - $acronym" => "Turno D - $acronym",
                                        ];
                                    })
                                    ->placeholder('Em caso de ser a turma toda, selecione o turno'),

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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('teacher.name')
                    ->label('Professor')
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->visible(fn() => Auth::user()?->isSuperAdmin())
                    ->wrap(),
                TextColumn::make('weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('timeperiod.description')
                    ->label('Hora da Aula')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->wrap()
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('classes.name')
                    ->label('Turma')
                    ->wrap()
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('room.building.name')
                    ->label('Pólo')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('room.name')
                    ->label('Sala')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        'Escalado' => 'info',
                        'Aprovado DP' => 'success',
                        'Recusado DP' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Aprovado' => 'Aprovado',
                        'Recusado' => 'Recusado',
                        'Escalado' => 'Escalado',
                        'Aprovado DP' => 'Aprovado DP',
                        'Recusado DP' => 'Recusado DP',
                        'Eliminado' => 'Eliminado',
                    ]),

                SelectFilter::make('teacher_id')
                    ->label('Professor')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->visible(fn() => auth()->user()?->isSuperAdmin()),

                SelectFilter::make('weekday_id')
                    ->label('Dia da Semana')
                    ->relationship('weekday', 'weekday'),

                SelectFilter::make('timeperiod_id')
                    ->label('Hora da Aula')
                    ->relationship('timeperiod', 'description'),

                SelectFilter::make('subject_id')
                    ->label('Disciplina')
                    ->relationship('subject', 'name'),

                SelectFilter::make('classes_id')
                    ->label('Turma')
                    ->relationship('classes', 'name'),

                SelectFilter::make('room_id')
                    ->label('Sala')
                    ->relationship('room', 'name'),

                SelectFilter::make('room.building_id')
                    ->label('Pólo')
                    ->relationship('room.building', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportar_selecionados')
                    ->label('Exportar Horários')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn() => self::exportSchedules())
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()?->isSuperAdmin()),

            ])
            ->bulkActions([

                BulkAction::make('exportar_selecionados')
                    ->label('Exportar Selecionados')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn(Collection $records) => self::exportSchedules($records))
                    ->visible(fn() => Auth::user()?->isSuperAdmin()),

            ]);
    }

    public static function rollbackScheduleRequest(Schedule $schedule): void
    {
        try {
            //ScheduleRequest::where('id_new_schedule', $schedule->id)->delete();
            ScheduleRequest::where('id_new_schedule', $schedule->id)->update(['status' => 'Eliminado']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao eliminar o pedido de troca de horário')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }


    public static function hoursCounterUpdate(Schedule $schedule, Bool $plusOrMinus): void
    {
        try {
            DB::transaction(function () use ($schedule, $plusOrMinus) {

                $schedule->load('subject');

                $tipo = strtolower(trim($schedule->subject->type ?? 'letiva'));

                $counter = TeacherHourCounter::where('id_teacher', $schedule->id_teacher)
                    ->where('id_schoolyear', $schedule->id_schoolyear)
                    ->first();

                if (!$counter) {
                    return;
                }

                if ($plusOrMinus) {
                    if ($tipo === 'Não Letiva' || $tipo === 'nao letiva' || $tipo === 'não letiva') {
                        $counter->non_teaching_load += 1;
                    } else {
                        $counter->teaching_load += 1;
                    }
                } else {
                    if ($tipo === 'Não Letiva' || $tipo === 'nao letiva' || $tipo === 'não letiva') {
                        $counter->non_teaching_load -= 1;
                    } else {
                        $counter->teaching_load -= 1;
                    }
                }

                $counter->workload = $counter->teaching_load + $counter->non_teaching_load;
                $counter->save();
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao atualizar a carga horária do professor')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
    public static function getRecordActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationSubjectResource\RelationManagers;
use App\Models\RegistrationSubject;
use App\Models\Schedule;
use Filament\Actions\EditAction;
use Filament\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ButtonGroup;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ButtonAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Illuminate\Container\Attributes\Log;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Carbon;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;

use function Laravel\Prompts\text;
use function Livewire\Volt\placeholder;

class RegistrationSubjectResource extends Resource
{
    protected static ?string $model = RegistrationSubject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Aluno';
    protected static ?string $navigationLabel = 'O meu Horário';

    public static function getLabel(): string
    {
        return 'O meu Horário';
    }

    public static function getPluralLabel(): string
    {
        return 'O meu Horário';
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Aluno');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Escolha o Turno')
                ->columns(1)
                ->schema(function ($record) {

                    $availableShifts = \App\Models\Schedule::query()
                        ->where('id_subject', $record->id_subject)
                        ->whereHas('classes', fn($q) => $q->where('classes.id', $record->registration->id_class))
                        ->where('status', 'Aprovado')
                        ->where('shift', 'like', 'Turno%') // começa com "Turno"
                        // Exemplo de filtro adicional
                        ->get();

                    $shiftCards = collect($availableShifts)->map(function ($s) {
                        $day = $s->weekday?->weekday ?? '';
                        $start = $s->timeperiod?->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $s->timeperiod->start_time)->format('H:i') : '';
                        $end   = $s->timeperiod?->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $s->timeperiod->end_time)->format('H:i') : '';
                        $room = $s->room?->name ?? '';
                        $inscritos = \App\Models\RegistrationSubject::where('shift', $s->id)
                            ->whereHas('registration', fn($q) => $q->where('id_class', $s->classes->pluck('id')))
                            ->count();
                        $vagas = max(0, $s->shift_limit - $inscritos);

                        return Section::make("👨‍🏫Professor: {$s->teacher?->name}")
                            ->extraAttributes([
                                'class' => 'bg-gray-50 border rounded-lg p-4 shadow mb-4 cursor-pointer hover:bg-blue-50 transition-colors'
                            ])
                            ->schema([
                                Placeholder::make("Turno {$s->shift}")
                                    ->label('🎯 Turno')
                                    ->content($s->shift ?? '-')
                                    ->extraAttributes(['class' => 'font-semibold text-gray-800']),
                                Placeholder::make("day_{$s->id}")
                                    ->label('📅 Dia')
                                    ->content($day ?: ' - '),
                                Placeholder::make("hour_{$s->id}")
                                    ->label('⏰ Horário')
                                    ->content("{$start}–{$end}"),
                                Placeholder::make("room_{$s->id}")
                                    ->label('🏫 Sala')
                                    ->content($room ?: '-'),
                                Placeholder::make("vagas_{$s->id}")
                                    ->label('👥 Vagas')
                                    ->content($vagas),

                                ToggleButtons::make('shift')
                                    ->label('Escolher')
                                    ->options([$s->id => 'Selecionar'])
                                    ->reactive()
                                    ->visible(fn() => $vagas > 0)
                                    ->dehydrated(fn() => $vagas > 0) // não envia o valor no submit quando não há vagas
                                    ->afterStateHydrated(function ($state, callable $set) use ($vagas) {
                                        if ($vagas <= 0) {
                                            $set('shift', null); // limpa seleção antiga
                                        }
                                    }),


                                ToggleButtons::make("sem_vagas_{$s->id}")
                                    ->label('Escolher')
                                    ->options(['sem' => 'Sem vagas'])        // um único “botão” com o texto
                                    ->colors(['sem' => 'danger'])            // vermelho nativo do Filament
                                    ->icons(['sem' => 'heroicon-m-no-symbol']) // ícone opcional
                                    ->inline()                               // visual de “pill”
                                    ->disabled()                             // não clicável
                                    ->dehydrated(false)                      // não submete nada
                                    ->visible(fn() => $vagas <= 0)
                                    ->columnSpanFull(),         // só aparece quando não há vagas

                            ])



                            ->columns(5);
                    })->toArray();

                    // Adiciona card "Nenhum turno"
                    $noneCard = Section::make("Nenhum turno")
                        ->extraAttributes([
                            'class' => 'bg-gray-50 border rounded-lg p-4 shadow mb-4 cursor-pointer hover:bg-red-50 transition-colors'
                        ])
                        ->schema([
                            ToggleButtons::make('shift')
                                ->label('Escolher')
                                ->options(['none' => 'Selecionar nenhum turno'])
                                ->reactive()
                        ]);

                    return array_merge($shiftCards, [$noneCard]);
                }),
        ]);
    }


    public static function table(Table $table): Table
    {

        return $table


            ->columns([

                TextColumn::make('subject.name')->label('Disciplina'),

                TextColumn::make('turno_display')
                    ->label('Horário')
                    ->badge()
                    ->extraAttributes(['style' => 'white-space: pre-line;']) // permite \n na descrição
                    ->state(function ($record) {
                        // selectedSchedule existe?
                        $hasSelected = method_exists($record, 'selectedSchedule')
                            ? $record->selectedSchedule()->exists()
                            : (bool) $record->selectedSchedule;

                        $selected = $record->selectedSchedule;

                        // 1) Se houver selectedSchedule, mostra o nome do docente (se existir)
                        if ($hasSelected) {
                            $name =  $selected?->teacher?->name;
                            if (! blank($name)) {
                                return 'Prof. ' . $name;
                            }
                            // Se não tiver docente, tenta pelo menos o turno; senão mostra “Turno por escolher”
                            return blank($selected?->shift) ? 'Turno por escolher' : ($selected->shift);
                        }

                        // 2) Fallback: procurar no Schedule pelo nº do aluno dentro de 'shift'
                        $studentNo = $record->student?->number
                            ?? $record->registration?->student?->number
                            ?? $record->number
                            ?? null;

                        if (! $studentNo) {
                            return 'Sem Turno';
                        }

                        $candidates = Schedule::query()
                            ->where('id_subject', $record->id_subject)
                            ->where('status', 'Aprovado')
                            ->where('shift', 'like', '%' . $studentNo . '%')
                            ->when(
                                $record->registration?->id_class,
                                fn($q, $id) =>
                                $q->whereHas('classes', fn($qq) => $qq->where('classes.id', $id))
                            )
                            ->with(['weekday', 'timeperiod', 'room', 'teacher'])
                            ->get()
                            // evitar falso match (ex.: 444 em 4444)
                            ->filter(fn($sch) => preg_match('/(^|\D)' . preg_quote($studentNo, '/') . '(\D|$)/', (string) $sch->shift))
                            ->values();

                        if ($candidates->isEmpty()) {
                            return 'Sem Turno';
                        }

                        // guarda para a description/cor
                        $record->foundSchedulesForTurno = $candidates;

                        // Badge: nomes dos docentes (até 2 + “+N”)
                        $names = $candidates->pluck('teacher.name')->filter()->unique()->values();

                        if ($names->isEmpty()) {
                            // fallback: mostra o turno do primeiro
                            return (string) ($candidates->first()->shift ?? 'Sem Turno');
                        }

                        return $names->count() <= 2
                            ? 'Prof. ' . $names->implode(' / ')
                            : $names->take(2)->implode(' / ') . ' +' . ($names->count() - 2);
                    })
                    ->color(function ($record) {
                        $hasSelected = method_exists($record, 'selectedSchedule')
                            ? $record->selectedSchedule()->exists()
                            : (bool) $record->selectedSchedule;

                        if ($hasSelected && ! blank($record->selectedSchedule?->shift)) {
                            return 'success';
                        }

                        if (isset($record->foundSchedulesForTurno) && $record->foundSchedulesForTurno->isNotEmpty()) {
                            return 'success';
                        }

                        return $hasSelected ? 'warning' : 'gray';
                    })
                    ->description(function ($record) {
                        $fmt = fn($t) => $t ? substr($t, 0, 5) : null; // HH:MM

                        // nº de aluno do registo (ajusta a origem se diferente)
                        $studentNo = $record->student?->number
                            ?? $record->registration?->student?->number
                            ?? $record->number
                            ?? null;

                        // helper: gera uma linha com as regras de Individual/Partilhada
                        $lineFor = function ($s) use ($fmt, $studentNo) {
                            $turno = (string) ($s->shift ?? '');

                            // Extrair todos os números do shift (na ordem, únicos)
                            $nums = collect();
                            if ($turno !== '') {
                                preg_match_all('/\d+/', $turno, $m);
                                $nums = collect($m[0])->map(fn($n) => (string) $n)->unique()->values();
                            }

                            $isIndividual = $studentNo && $nums->count() === 1 && $nums->first() === (string) $studentNo;

                            // Construir pedaços base
                            $dia  = $s->weekday?->weekday ?: '—';
                            $hora = ($fmt($s->timeperiod?->start_time) && $fmt($s->timeperiod?->end_time))
                                ? $fmt($s->timeperiod?->start_time) . '–' . $fmt($s->timeperiod?->end_time)
                                : '—';
                            $sala = $s->room?->name ?: '—';

                            // Sufixos "Aula Individual / Partilhada"
                            $suffix = '';
                            if ($studentNo && $nums->isNotEmpty()) {
                                $others = $nums->filter(fn($n) => $n !== (string) $studentNo)->values();
                                if ($others->isEmpty()) {
                                    $suffix = ' (Aula Individual)';
                                } else {
                                    $lista = $others->implode(', ');
                                    $suffix = ' (Aula Partilhada com ' . ($others->count() === 1 ? 'Aluno nº ' : 'Alunos nº ') . $lista . ')';
                                }
                            }

                            // Se for Individual: não mostrar "Turno …"
                            if ($isIndividual) {
                                return "{$dia} ● {$hora} ● {$sala}{$suffix}";
                            }

                            // Caso normal (ou partilhada): inclui Turno
                            $turnoShow = (stripos($turno, 'Turno') === 0) ? $turno . '  ● ' : '';

                            return "{$turnoShow} {$dia}  ●  {$hora}  ● {$sala}{$suffix}";
                        };

                        // 1) selectedSchedule → 1 linha
                        if ($record->selectedSchedule) {
                            return $lineFor($record->selectedSchedule);
                        }

                        // 2) fallback → TODAS as marcações (já guardadas em foundSchedulesForTurno)
                        if (isset($record->foundSchedulesForTurno) && $record->foundSchedulesForTurno->isNotEmpty()) {
                            return $record->foundSchedulesForTurno->map(fn($s) => $lineFor($s))->implode("\n");
                        }

                        return '';
                    })
                    ->extraAttributes(['style' => 'white-space: pre-line;']) // para \n virar múltiplas linhas
                    ->sortable(false)
                    ->searchable(false),


                //----

            ])->actions([
                // Tables\Actions\EditAction::make('selectTurno')
                //     ->label('Selecionar Turno')
                //     ->visible(fn($record) => (bool) $record->subject?->student_can_enroll),



                // 1) Selecionar Turno — só quando pode inscrever e a janela está aberta
                Tables\Actions\EditAction::make('selectTurno')
                    ->label('Selecionar Turno')
                    ->visible(function ($record) {
                        $canEnroll = (bool) $record->subject?->student_can_enroll;
                        $sy = $record->registration?->schoolyear;

                        if (! $canEnroll || ! $sy || ! $sy->active) {
                            return false;
                        }

                        $now    = Carbon::now()->startOfDay();
                        $start  = $sy->start_date_registration
                            ? Carbon::parse($sy->start_date_registration)->startOfDay() : null;
                        $end    = $sy->end_date_registration
                            ? Carbon::parse($sy->end_date_registration)->endOfDay() : null;

                        $open = ($start && $now->greaterThanOrEqualTo($start))
                            && (is_null($end) || $now->lessThanOrEqualTo($end));

                        return $open;
                    }),

                // 2) Período de inscrição — só quando pode inscrever MAS a janela NÃO está aberta
                Action::make('verPeriodo')
                    ->label('Período de inscrição')
                    ->icon('heroicon-m-information-circle')
                    ->color(function ($record) {
                        // amarelo se ainda não abriu; vermelho se já terminou / sem datas
                        $sy = $record->registration?->schoolyear;
                        if (! $sy) return 'danger';

                        $now   = Carbon::now()->startOfDay();
                        $start = $sy->start_date_registration
                            ? Carbon::parse($sy->start_date_registration)->startOfDay() : null;
                        $end   = $sy->end_date_registration
                            ? Carbon::parse($sy->end_date_registration)->endOfDay() : null;

                        if ($start && $now->lt($start)) return 'warning';
                        return 'danger';
                    })
                    ->visible(function ($record) {
                        $canEnroll = (bool) $record->subject?->student_can_enroll;
                        $sy = $record->registration?->schoolyear;

                        if (! $canEnroll || ! $sy || ! $sy->active) {
                            return false;
                        }

                        $now    = Carbon::now()->startOfDay();
                        $start  = $sy->start_date_registration
                            ? Carbon::parse($sy->start_date_registration)->startOfDay() : null;
                        $end    = $sy->end_date_registration
                            ? Carbon::parse($sy->end_date_registration)->endOfDay() : null;

                        $open = ($start && $now->greaterThanOrEqualTo($start))
                            && (is_null($end) || $now->lessThanOrEqualTo($end));

                        // mostra esta ação apenas quando a janela NÃO está aberta
                        return ! $open;
                    })
                    ->modalHeading('Período de inscrição indisponível')
                    ->modalIcon('heroicon-m-no-symbol')
                    ->modalDescription(function ($record) {
                        $sy = $record->registration?->schoolyear;

                        if (! $sy || ! $sy->active) {
                            return "Não se encontra período de inscrição ativo.";
                        }

                        $now   = Carbon::now()->startOfDay();
                        $start = $sy->start_date_registration
                            ? Carbon::parse($sy->start_date_registration)->startOfDay() : null;
                        $end   = $sy->end_date_registration
                            ? Carbon::parse($sy->end_date_registration)->endOfDay() : null;

                        $startStr = $start ? $start->format('d/m/Y') : '—';
                        $endStr   = $end   ? $end->format('d/m/Y')   : '—';

                        if ($start && $now->lt($start)) {
                            return "Não se encontra período de inscrição ativo.\n"
                                . "Janela definida: {$startStr} a {$endStr}.\n"
                                . "Abre em {$start->diffForHumans($now, true)}.";
                        }

                        if ($end && $now->gt($end)) {
                            return "Não se encontra período de inscrição ativo.\n"
                                . "Janela decorreu de {$startStr} a {$endStr}.\n"
                                . "Terminou há {$end->diffForHumans($now, true)}.";
                        }

                        // Sem datas válidas configuradas
                        return "Não se encontra período de inscrição ativo.";
                    })
                    ->modalSubmitAction(false), // modal apenas informativo
            ])
        ;
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas(
                'registration.student',
                fn($q) =>
                $q->where('user_id', Auth::id())
            )
            ->whereHas(
                'registration.schoolyear',
                fn($q) =>
                $q->where('active', true)
            );
    }



    public static function getRelations(): array
    {
        return [
            //RelationManagers\SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => RegistrationSubjectResource\Pages\ListRegistrationSubjects::route('/'),
            //    'edit' => RegistrationSubjectResource\Pages\EditRegistrationSubject::route('/{record}/edit'),

            //'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}

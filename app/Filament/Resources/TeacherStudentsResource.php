<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherStudentsResource\Pages;
use App\Models\RegistrationSubject;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use Filament\Actions\ExportAction as ActionsExportAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;

class TeacherStudentsResource extends Resource
{
    // ✅ 1 linha = 1 Aluno/Disciplina (pivot)
    protected static ?string $model = RegistrationSubject::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Os meus Alunos';
    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?int    $navigationSort  = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Professor');
    }

    public static function getPluralLabel(): string
    {
        return 'Os meus Alunos';
    }

    public static function getEloquentQuery(): Builder
    {
        $teacherId  = auth()->user()?->teacher?->id ?? 0;
        $activeYear = SchoolYear::query()->where('active', true)->first();

        if (! $teacherId || ! $activeYear) {
            return parent::getEloquentQuery()->whereRaw('1=0');
        }

        // Disciplinas lecionadas por este professor no ano ativo
        $teacherSubjectIds = TeacherSubject::query()
            ->where('id_teacher', $teacherId)
            ->where('id_schoolyear', $activeYear->id)
            ->pluck('id_subject');

        // Se não houver disciplinas, retorna query vazia
        if ($teacherSubjectIds->isEmpty()) {
            return parent::getEloquentQuery()->whereRaw('1=0');
        }





        return parent::getEloquentQuery()
            ->whereIn('id_subject', $teacherSubjectIds)
            ->whereHas('registration', fn($q) => $q->where('id_schoolyear', $activeYear->id))
            ->with([
                'subject',
                'registration.student',  // ->number (ou process_number)
                'registration.class',    // ->name
            ]);
    }




    public static function exportTeacherStudents(?Collection $records = null): StreamedResponse
    {
        // 1) Ano letivo ativo
        $anoLetivoAtivoId = class_exists(\App\Helpers\DBHelper::class)
            ? \App\Helpers\DBHelper::getIDActiveSchoolyear()
            : \App\Models\SchoolYear::where('active', true)->value('id');

        $teacherId = auth()->user()?->teacher?->id ?? 0;

        // 2) Obter registos (selecionados vs todos no escopo)
        if ($records) {
            $regSubs = $records->load(['registration.student', 'registration.class', 'subject']);
        } else {
            $teacherSubjectIds = \App\Models\TeacherSubject::query()
                ->where('id_teacher', $teacherId)
                ->where('id_schoolyear', $anoLetivoAtivoId)
                ->pluck('id_subject');

            $regSubs = RegistrationSubject::query()
                ->whereIn('id_subject', $teacherSubjectIds)
                ->whereHas('registration', fn($q) => $q->where('id_schoolyear', $anoLetivoAtivoId))
                ->with(['registration.student', 'registration.class', 'subject'])
                ->get();
        }

        // 3) Helper para resolver o turno (mesma lógica usada na tabela)
        $resolveShift = function (RegistrationSubject $record) use ($teacherId): ?string {
            $classId   = $record->registration?->id_class;
            $subjectId = $record->id_subject;
            $studentNo = (string) ($record->registration?->student?->number ?? '');

            if ($teacherId && $classId && $subjectId && $studentNo !== '') {
                $schedule = Schedule::query()
                    ->where('id_teacher', $teacherId)
                    ->where('id_subject', $subjectId)
                    ->where('status', 'Aprovado')
                    ->whereHas('classes', fn($q) => $q->where('classes.id', $classId))
                    ->where('shift', 'like', '%' . $studentNo . '%')
                    ->orderBy('id')
                    ->first();

                if ($schedule) {
                    $shift = (string) ($schedule->shift ?? '');
                    if ($shift !== '' && preg_match('/(^|\D)' . preg_quote($studentNo, '/') . '(\D|$)/', $shift)) {
                        return $shift;
                    }
                }
            }

            // fallback: pivot
            $regSub = RegistrationSubject::query()
                ->where('id_registration', $record->id_registration)
                ->where('id_subject', $record->id_subject)
                ->first();

            if ($regSub) {
                $scheduleId = $regSub->id_schedule ?? $regSub->schedule_id ?? null;

                if (! $scheduleId) {
                    foreach (['shift', 'turno', 'turn'] as $cand) {
                        $val = $regSub->{$cand} ?? null;
                        if ($val !== null && ctype_digit((string) $val)) {
                            $scheduleId = (int) $val;
                            break;
                        }
                    }
                }

                if ($scheduleId) {
                    $sch = Schedule::find($scheduleId);
                    if ($sch && ! blank($sch->shift)) {
                        return (string) $sch->shift;
                    }
                }

                foreach (['shift', 'turno', 'turn'] as $cand) {
                    $val = $regSub->{$cand} ?? null;
                    if (! blank($val) && ! ctype_digit((string) $val)) {
                        return (string) $val;
                    }
                }
            }

            return null;
        };

        // 4) Stream .txt pipe-separated
        $now = now()->format('Y-m-d_H-i');
        $filename = "os-meus-alunos-{$now}.txt";

        return response()->streamDownload(function () use ($regSubs, $resolveShift) {
            $h = fopen('php://output', 'w');

            // cabeçalho
            $header = ['Aluno', 'Disciplina', 'Turma', 'Turno'];
            fputs($h, implode(';', array_map(fn($v) => '' . str_replace('', '', $v) . '', $header)) . "\n");

            foreach ($regSubs as $r) {
                $aluno  = (string) ($r->registration?->student?->name ?? ';');
                $disc   = (string) ($r->subject?->name ?? ';');
                $turma  = (string) ($r->registration?->class?->name ?? ';');
                $turno  = (string) ($resolveShift($r) ?? ';');

                $linha = [$aluno, $disc, $turma, $turno];
                $linha = array_map(fn($v) => '' . str_replace('', '', (string) $v) . '', $linha);

                fputs($h, implode(';', $linha) . "\n");
            }

            fclose($h);
        }, $filename, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id_registration')
            ->columns([
                // Aluno
                TextColumn::make('registration.student.name')
                    ->label('Aluno')
                    ->description(
                        fn(RegistrationSubject $r) =>
                        'Nº ' . ($r->registration?->student?->number ?? '—')
                    )
                    ->sortable()
                    ->searchable(),

                // Disciplina
                TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->sortable()
                    ->searchable(),

                // Turma
                TextColumn::make('registration.class.name')
                    ->label('Turma')
                    ->sortable()
                    ->searchable(),

                // Turno (Schedule do professor → fallback pivot)
                TextColumn::make('shift')
                    ->label('Turno')
                    ->state(fn(RegistrationSubject $record) => self::resolveShift($record) ?? '—')
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_subject')
                    ->label('Disciplina')
                    ->relationship('subject', 'name')
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('id_class')
                    ->label('Turma')
                    ->options(fn() => DB::table('classes')->pluck('name', 'id')->toArray())
                    ->query(
                        fn(Builder $q, array $data) =>
                        ! empty($data['value'])
                            ? $q->whereHas('registration', fn($qq) => $qq->where('id_class', $data['value']))
                            : $q
                    ),
            ])
            ->actions([])
            ->headerActions([
                Tables\Actions\Action::make('exportar_tudo')
                    ->label('Exportar')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn() => static::exportTeacherStudents()),
            ])
            ->bulkActions([

                BulkAction::make('exportar_selecionados')
                    ->label('Exportar selecionados')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn(Collection $records) => static::exportTeacherStudents($records)),
            ]);
    }

    /**
     * Resolve o texto do turno:
     *  1) Procura Schedule do professor (disciplina+turma) contendo o nº do aluno.
     *  2) Fallback: usa id_schedule do pivot ou campo textual (shift/turno/turn).
     */
    protected static function resolveShift(RegistrationSubject $record): ?string
    {
        $teacherId = auth()->user()?->teacher?->id ?? 0;
        $classId   = $record->registration?->id_class;
        $subjectId = $record->id_subject;
        $studentNo = (string) ($record->registration?->student?->number ?? '');

        // 1) Schedule do professor (preferencial)
        if ($teacherId && $classId && $subjectId && $studentNo !== '') {
            $schedule = Schedule::query()
                ->where('id_teacher', $teacherId)
                ->where('id_subject', $subjectId)
                ->where('status', 'Aprovado')
                ->whereHas('classes', fn($q) => $q->where('classes.id', $classId))
                ->where('shift', 'like', '%' . $studentNo . '%')
                ->orderBy('id')
                ->first();

            if ($schedule) {
                $shift = (string) ($schedule->shift ?? '');
                // Evita falso match 444 vs 4444
                if ($shift !== '' && preg_match('/(^|\D)' . preg_quote($studentNo, '/') . '(\D|$)/', $shift)) {
                    return $shift;
                }
            }
        }

        // 2) Fallback no pivot: id_schedule → texto
        $regSub = RegistrationSubject::query()
            ->where('id_registration', $record->id_registration)
            ->where('id_subject', $subjectId)
            ->first();

        if ($regSub) {
            // (a) id_schedule explícito (ou schedule_id)
            $scheduleId = $regSub->id_schedule ?? $regSub->schedule_id ?? null;

            // (b) se algum campo textual tiver só dígitos, trata como id_schedule
            if (! $scheduleId) {
                foreach (['shift', 'turno', 'turn'] as $cand) {
                    $val = $regSub->{$cand} ?? null;
                    if ($val !== null && ctype_digit((string) $val)) {
                        $scheduleId = (int) $val;
                        break;
                    }
                }
            }

            if ($scheduleId) {
                $sch = Schedule::find($scheduleId);
                if ($sch && ! blank($sch->shift)) {
                    return (string) $sch->shift;
                }
            }

            // (c) texto do turno no pivot
            foreach (['shift', 'turno', 'turn'] as $cand) {
                $val = $regSub->{$cand} ?? null;
                if (! blank($val) && ! ctype_digit((string) $val)) {
                    return (string) $val;
                }
            }
        }

        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherStudents::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use App\Models\SchoolYear;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\User;
use Filament\Facades\Filament;


class OverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.overview-widget';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    protected int|string|array $pollingInterval = '5s';

    protected int | string | array $columnSpan = [
        'sm' => 12,
        'md' => 12,
        'lg' => 'full',
    ];

    public function render(): View
    {
        $userId = Filament::auth()->id();
        $teacher = Teacher::where('id_user', $userId)->first();

        if (! $teacher) {
            return view(static::$view, ['resumo' => []]);
        }

        // Obter ano letivo ativo
        $anoLetivoAtivo = SchoolYear::where('active', true)->first();

        if (! $anoLetivoAtivo) {
            return view(static::$view, ['resumo' => []]);
        }

        // Marcações aprovadas no ano letivo ativo
        $schedules = Schedule::with('subject')
            ->whereIn('status', ['Aprovado', 'Aprovado DP'])
            ->where('id_teacher', $teacher->id)
            ->where('id_schoolyear', $anoLetivoAtivo->id)
            ->get();

        // Contador de carga horária
        $counter = TeacherHourCounter::where('id_teacher', $teacher->id)
            ->where('id_schoolyear', $anoLetivoAtivo->id)
            ->first();

        //$letivaDisponivel = $counter?->teaching_load ?? 0;
        //$naoLetivaDisponivel = $counter?->non_teaching_load ?? 0;

        // Aulas
        $aulasLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'letiva')->count();
        $aulasNaoLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'nao letiva')->count();

        $cargos = $teacher->positions()
            ->where('id_schoolyear', $anoLetivoAtivo->id)
            ->get()
            ->map(function ($cargo) {
                return [
                    'nome' => $cargo->name,
                    'descricao' => $cargo->description ?? 'Cargo sem descrição',
                    'redução_letiva' => $cargo->reduction_l ?? 0,
                    'redução_naoletiva' => $cargo->reduction_nl ?? 0,
                ];
            })->toArray();

        $tempoReducoes = $teacher->timeReductions()
            ->where('id_schoolyear', $anoLetivoAtivo->id)
            ->get()
            ->map(function ($tempoReducoes) {
                return [
                    'nome' => $tempoReducoes->name,
                    'descricao' => $tempoReducoes->description ?? 'Cargo sem descrição',
                    'redução_letiva' => $tempoReducoes->value_l ?? 0,
                    'redução_naoletiva' => $tempoReducoes->value_nl ?? 0,
                ];
            })->toArray();

        $resumo = [
            'letiva' => $aulasLetivas,
            'nao_letiva' => $aulasNaoLetivas,
            'disponivel_letiva' => max(0, $counter?->teaching_load ?? 0),
            'disponivel_naoletiva' => max(0, $counter?->non_teaching_load ?? 0),
            'cargos' => $cargos,
            'tempo_reducoes' => $tempoReducoes,
        ];

        return view(static::$view, compact('resumo'));
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Professor');
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\Position;
use App\Models\TimeReduction;
use Filament\Facades\Filament;


class OverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.overview-widget';
    protected static ?int $sort = 2;

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

        // Marcações aprovadas
        $schedules = Schedule::with('subject')
            ->whereIn('status', ['Aprovado', 'Aprovado DP'])
            ->where('id_teacher', $teacher->id)
            ->get();

        // Contador
        $counter = TeacherHourCounter::where('id_teacher', $teacher->id)->first();
        //  $disponivel = $counter?->carga_horaria ?? 0;
        $letivaDisponivel = $counter?->teaching_load ?? 0;
        $naoLetivaDisponivel = $counter?->non_teaching_load ?? 0;

        // Aulas
        $aulasLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'letiva')->count();
        $aulasNaoLetivas = $schedules->filter(fn($s) => strtolower($s->subject->type ?? '') === 'nao letiva')->count();

        // Cargos com redução
        $cargos = Position::with('position')
            ->where('id', $teacher->id)
            ->get()
            ->map(function ($cargo) {
                return [
                    'nome' => $cargo->position->name,
                    'descricao' => $cargo->position->description ?? 'Cargo sem descrição',
                    'redução_letiva' => $cargo->position->reduction_l ?? 0,
                    'redução_naoletiva' => $cargo->position->reduction_nl ?? 0,
                ];
            })->toArray();

        // Reduções por tempo de serviço
        $tempoReducoes = TimeReduction::with('timeReduction')
            ->where('id', $teacher->id)
            ->get()
            ->map(function ($reducao) {
                return [
                    'nome' => $reducao->timeReduction->name ?? 'Redução sem nome',
                    'descricao' => $reducao->timeReduction->description ?? 'Redução sem descrição',
                    'redução_letiva' => $reducao->timeReduction->value_l ?? 0,
                    'redução_naoletiva' => $reducao->timeReduction->value_nl ?? 0,
                ];
            })->toArray();
        // dd($cargos, $tempoReducoes);
        $resumo = [
            //    'disponivel' => $disponivel,
            'letiva' => $aulasLetivas,
            'nao_letiva' => $aulasNaoLetivas,
            'disponivel_letiva' => max(0, $letivaDisponivel),
            'disponivel_naoletiva' => max(0, $naoLetivaDisponivel),
            'cargos' => $cargos,
            'tempo_reducoes' => $tempoReducoes,
        ];

        return view(static::$view, compact('resumo'));
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof \App\Models\User && $user->hasRole('Professor');
    }
}

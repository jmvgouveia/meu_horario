<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Timeperiod;
use App\Models\Weekday;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class WeeklyScheduleWidget extends Widget
{
    protected static string $view = 'filament.widgets.weekly-schedule-widget';
    protected static bool $isLazy = false; // Para garantir que carrega completamente

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
            // Se não tem professor vinculado, retorna view vazia
            return view(static::$view, [
                'calendar' => [],
                'weekdays' => [],
                'timePeriods' => [],
            ]);
        }

        // Busca as marcações aprovadas do professor
        $schedules = Schedule::with(['room', 'weekday', 'timePeriod', 'subject', 'classes'])
            ->where('id_teacher', $teacher->id)
            ->whereNotIn('status', ['Recusado DP', 'Eliminado'])
            ->orderByRaw("
                CASE status
                    WHEN 'Aprovado' THEN 1
                    WHEN 'Pendente' THEN 2
                    ELSE 3
                END
            ") // Ordena Aprovado primeiro, depois Pendente
            ->get();

        // Pegamos os dias da semana da tabela Weekday (ajusta se for outro nome)
        $weekdays = Weekday::orderBy('id')->pluck('weekday')->toArray();

        // Pegamos todos os períodos de tempo ordenados
        $timePeriods = Timeperiod::orderBy('description')->get();

        // Monta matriz vazia do calendário: [periodo][dia] => schedule|null
        $calendar = [];

        foreach ($timePeriods as $tp) {
            foreach (array_keys($weekdays) as $dayId) {
                $calendar[$tp->id][$dayId] = [];
            }
        }
        // Preenche o calendário com as marcações do professor
        foreach ($schedules as $schedule) {
            $dayId = $schedule->id_weekday;
            $timeId = $schedule->id_timeperiod;
            $calendar[$timeId][$dayId][] = $schedule;
        }

        $recusados = \App\Models\ScheduleRequest::where('status', 'Recusado')
            ->where('id_teacher_requester', $teacher->id)
            ->get()
            ->keyBy('id_schedule_novo');

        $escalados = \App\Models\ScheduleRequest::where('status', 'Escalado')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule_conflict] = $req;
                $carry[$req->id_schedule_novo] = $req;
                return $carry;
            }, collect());

        $PedidosAprovadosDP = \App\Models\ScheduleRequest::where('status', 'Aprovado DP')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule_conflict] = $req;
                $carry[$req->id_schedule_novo] = $req;
                return $carry;
            }, collect());

        $AprovadosDP = Schedule::where('status', 'Aprovado DP')
            ->get()
            ->keyBy('id');

        // Retorna a view com o calendário, dias da semana e períodos de tempo
        return view(static::$view, compact('calendar', 'weekdays', 'timePeriods', 'recusados', 'escalados', 'PedidosAprovadosDP', 'AprovadosDP'))
            ->with('teacher', $teacher);
    }
/* 
    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof \App\Models\User && $user->hasRole('Professor');
    } */
}

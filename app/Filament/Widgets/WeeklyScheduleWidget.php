<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\Timeperiod;
use App\Models\User;
use App\Models\Weekday;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class WeeklyScheduleWidget extends Widget
{
    protected static string $view = 'filament.widgets.weekly-schedule-widget';
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

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

            return view(static::$view, [
                'calendar' => [],
                'weekdays' => [],
                'timePeriods' => [],
            ]);
        }

        // Obter o ano letivo ativo
        $anoLetivoAtivo = SchoolYear::where('active', true)->first();

        // Se não houver ano ativo, retorna vazio
        if (! $anoLetivoAtivo) {
            return view(static::$view, [
                'calendar' => [],
                'weekdays' => [],
                'timePeriods' => [],
            ]);
        }

        // Horários do professor no ano letivo ativo
        $schedules = Schedule::with(['room', 'weekday', 'timeperiod', 'subject', 'classes'])
            ->where('id_teacher', $teacher->id)
            ->where('id_schoolyear', $anoLetivoAtivo->id)
            ->whereNotIn('status', ['Recusado DP', 'Eliminado'])
            ->orderByRaw("
            CASE status
                WHEN 'Aprovado' THEN 1
                WHEN 'Pendente' THEN 2
                ELSE 3
            END
        ")
            ->get();

        $weekdays = Weekday::orderBy('id')->pluck('weekday')->toArray();

        $timePeriods = Timeperiod::orderBy('description')->get();

        $calendar = [];

        foreach ($timePeriods as $tp) {
            foreach (array_keys($weekdays) as $dayId) {
                $calendar[$tp->id][$dayId] = [];
            }
        }
        foreach ($schedules as $schedule) {
            $dayId = $schedule->id_weekday;
            $timeId = $schedule->id_timeperiod;
            $calendar[$timeId][$dayId][] = $schedule;
        }

        $recusados = ScheduleRequest::where('status', 'Recusado')
            ->where('id_teacher', $teacher->id)
            ->get()
            ->keyBy('id_new_schedule');

        $escalados = ScheduleRequest::where('status', 'Escalado')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule] = $req;
                $carry[$req->id_new_schedule] = $req;
                return $carry;
            }, collect());

        $PedidosAprovadosDP = ScheduleRequest::where('status', 'Aprovado DP')
            ->get()
            ->reduce(function ($carry, $req) {
                $carry[$req->id_schedule] = $req;
                $carry[$req->id_new_schedule] = $req;
                return $carry;
            }, collect());

        $AprovadosDP = Schedule::where('status', 'Aprovado DP')
            ->get()
            ->keyBy('id');

        return view(static::$view, compact('calendar', 'weekdays', 'timePeriods', 'recusados', 'escalados', 'PedidosAprovadosDP', 'AprovadosDP'))
            ->with('teacher', $teacher);
    }



    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Professor');
    }
}

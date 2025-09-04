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

        $timePeriods = Timeperiod::orderBy('start_time')->get()->values(); //

        //    $timePeriods = TimePeriod::where('active', true)
        // ->orderBy('start_time')
        // ->get()
        // ->values();

        $calendar = [];



        foreach ($timePeriods as $period) {

            $start = \Carbon\Carbon::createFromFormat('H:i:s', $period->start_time);
            $label = $start->format('H:00'); // agrupamento por hora cheia

            $timePeriodsGrouped[$label][] = $period;
        }




        // foreach ($timePeriods as $tp) {
        //     foreach (array_keys($weekdays) as $dayId) {
        //         $calendar[$tp->id][$dayId] = [];
        //     }
        // }


        // foreach ($schedules as $schedule) {
        //     $dayId = $schedule->id_weekday;
        //     $timeId = $schedule->id_timeperiod;
        //     $calendar[$timeId][$dayId][] = $schedule;
        // }

        // foreach ($schedules as $schedule) {
        //     $dayId = $schedule->id_weekday;
        //     $timeId = $schedule->id_timeperiod;

        //     // Marcar o slot da própria meia hora
        //     $calendar[$timeId][$dayId][] = $schedule;

        //     // Garantir que timeperiod está carregado
        //     if (!$schedule->relationLoaded('timeperiod') || !$schedule->timeperiod?->start_time) {
        //         continue;
        //     }

        //     $startMin = \Carbon\Carbon::parse($schedule->timeperiod->start_time)->minute;

        //     // Se começa às 30, ocupar a slot seguinte (formando 1h visual)
        //     if ($startMin === 30) {
        //         $currentIndex = $timePeriods->search(fn($tp) => $tp->id === $timeId);

        //         if ($currentIndex !== false && isset($timePeriods[$currentIndex + 1])) {
        //             $nextSlot = $timePeriods[$currentIndex + 1];
        //             $calendar[$nextSlot->id][$dayId][] = $schedule;
        //         }
        //     }
        // }
        // foreach ($timePeriods as $i => $tp) {
        //     dump("Slot $i → ID: {$tp->id}, Início: {$tp->start_time}");
        // }
        foreach ($schedules as $schedule) {
            $dayId = $schedule->id_weekday;
            $timeId = $schedule->id_timeperiod;

            $startMin = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->timeperiod->start_time)->minute;
            $currentIndex = $timePeriods->search(fn($tp) => $tp->id === $timeId);

            // Começa às :00 → ocupar slot atual + slot seguinte
            if ($startMin === 0) {
                $calendar[$timeId][$dayId][] = $schedule;

                $nextSlot = $timePeriods[$currentIndex + 1] ?? null;
                if ($nextSlot) {
                    $calendar[$nextSlot->id][$dayId][] = $schedule;
                }
            }

            // Começa às :30 → ocupar slot atual + slot1 da hora seguinte
            if ($startMin === 30) {
                $calendar[$timeId][$dayId][] = $schedule;

                $nextSlot = $timePeriods[$currentIndex + 1] ?? null;
                if ($nextSlot) {
                    $calendar[$nextSlot->id][$dayId][] = $schedule;
                }
            }
        }


        // foreach ($calendar as $timeId => $slots) {
        //     foreach ($slots as $dayId => $items) {
        //         foreach ($items as $sched) {
        //             dump("{$sched->timeperiod->start_time} | dia: $dayId | slot: $timeId | id: {$sched->id}");
        //         }
        //     }
        // }
        // dd('Fim do debug');
        // foreach ($schedules as $schedule) {
        //     $dayId = $schedule->id_weekday;
        //     $timeId = $schedule->id_timeperiod;

        //     // Slot atual
        //     $calendar[$timeId][$dayId][] = $schedule;

        //     // Verifica se começa numa meia hora (ex: 08:30)
        //     $startMin = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->timeperiod->start_time)->minute;

        //     if ($startMin === 30) {
        //         // Localiza o ID do slot seguinte
        //         $currentIndex = $timePeriods->search(fn($tp) => $tp->id === $timeId);
        //         $nextSlot = $timePeriods[$currentIndex + 1] ?? null;

        //         if ($nextSlot) {
        //             $calendar[$nextSlot->id][$dayId][] = $schedule;
        //         }
        //     }
        // }


        //    $timePeriodsGrouped = array_filter($timePeriodsGrouped, fn($group) => count($group) === 2);

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

        // return view(static::$view, compact('calendar', 'weekdays', 'timePeriods', 'recusados', 'escalados', 'PedidosAprovadosDP', 'AprovadosDP'))
        //     ->with('teacher', $teacher);
        // dd($calendar);
        return view(static::$view, [
            'calendar' => $calendar,
            'weekdays' => $weekdays,
            'timePeriods' => $timePeriods->values(), // 👈 reinicia índices para [0, 1, 2...]
            'recusados' => $recusados,
            'escalados' => $escalados,
            'PedidosAprovadosDP' => $PedidosAprovadosDP,
            'AprovadosDP' => $AprovadosDP,
            'teacher' => $teacher,
        ]);
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasRole('Professor');
    }
}

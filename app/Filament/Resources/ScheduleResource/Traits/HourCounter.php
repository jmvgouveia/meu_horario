<?php

namespace App\Filament\Resources\ScheduleResource\Traits;

use App\Models\Schedule;
use App\Models\TeacherHourCounter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

trait HourCounter {

    protected function hoursCounterUpdate(Schedule $schedule, Bool $plusOrMinus): void
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

}
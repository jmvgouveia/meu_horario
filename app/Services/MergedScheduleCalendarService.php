<?php
// app/Services/MergedScheduleCalendarService.php

namespace App\Services;

use App\Models\Weekday;
use App\Models\Timeperiod;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Support\Facades\Schema;

class MergedScheduleCalendarService
{
    public static function buildForTeachers(array $teacherIds): array
    {
        $teacherIds = array_values(array_unique(array_filter($teacherIds, fn($v) => !empty($v))));

        // Descobrir nomes de colunas reais
        $table = (new Schedule)->getTable();
        $colTeacher    = Schema::hasColumn($table, 'teacher_id')    ? 'teacher_id'    : 'id_teacher';
        $colWeekday    = Schema::hasColumn($table, 'weekday_id')    ? 'weekday_id'    : 'id_weekday';
        $colTimeperiod = Schema::hasColumn($table, 'timeperiod_id') ? 'timeperiod_id' : 'id_timeperiod';

        // Dados base
        $weekdays    = Weekday::query()->orderBy('id')->pluck('weekday', 'id')->toArray();
        $timePeriods = Timeperiod::query()->orderBy('start_time')->get();

        // Eager load mínimo e seguro (usa nomes de relações já criadas no modelo)
        $with = array_values(array_intersect(
            ['timeperiod', 'weekday', 'teacher', 'subject', 'room'],
            // só carrega as relações que de facto existem como métodos
            collect(get_class_methods(Schedule::class))->filter()->all()
        ));

        $schedules = Schedule::query()
            ->with($with)
            ->whereIn($colTeacher, $teacherIds)
            ->where('status', 'Aprovado')
            ->get();

        // Montar calendar[timeperiod_id][weekday_id] = [schedules...]
        $calendar = [];
        foreach ($schedules as $s) {
            $tp = $s->{$colTimeperiod};
            $wd = $s->{$colWeekday};
            $calendar[$tp][$wd] ??= [];
            $calendar[$tp][$wd][] = $s;
        }

        // Paleta de cores por docente
        $teachers = Teacher::whereIn('id', $teacherIds)->get(['id', 'name']);
        $teacherPalette = [];
        foreach ($teachers as $t) {
            $teacherPalette[$t->id] = self::hslFromId($t->id);
        }

        // compat: arrays vazios para badges (se não usados, ignore)
        $recusados = $PedidosAprovadosDP = $escalados = [];

        return compact('weekdays', 'timePeriods', 'calendar', 'teacherPalette', 'teachers', 'recusados', 'PedidosAprovadosDP', 'escalados');
    }

    protected static function hslFromId(int $id): string
    {
        $h = ($id * 47) % 360;
        return "hsl({$h} 70% 45%)";
    }
}

<div id="merged-schedule-grid" wire:poll.30s>
    <style>
        #merged-schedule-grid .bg-inactive-slot {
            background-color: #f3f4f6;
            color: #9ca3af;
            font-style: italic;
        }

        #merged-schedule-grid .lesson-block {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            min-width: 0;
        }

        #merged-schedule-grid .hour-cell {
            display: grid;
            grid-template-rows: repeat(2, minmax(3rem, 1fr));
            height: 100%;
            min-width: 0;
        }

        #merged-schedule-grid .half-slot {
            padding: .25rem 0;
        }

        #merged-schedule-grid .half-slot + .half-slot {
            border-top: 1px dashed #cbd5e1;
        }

        #merged-schedule-grid .time-label {
            width: 4.25rem;
            min-width: 4.25rem;
            padding: .5rem .25rem;
            text-align: center;
            vertical-align: middle;
        }

        #merged-schedule-grid .sched-pill {
            border-radius: .5rem;
            padding: .35rem .5rem;
            color: #fff;
            font-weight: 600;
            font-size: .70rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        }

        #merged-schedule-grid .sched-left {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: 0;
        }

        #merged-schedule-grid .teacher-dot {
            width: .65rem;
            height: .65rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, .75);
            flex: 0 0 auto;
        }

        #merged-schedule-grid .sched-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 24ch;
        }

        @media (max-width: 768px) {
            #merged-schedule-grid .time-label {
                width: 3.5rem;
                min-width: 3.5rem;
                font-size: .75rem;
            }

            #merged-schedule-grid .sched-pill {
                padding: .3rem .35rem;
                font-size: .62rem;
            }

            #merged-schedule-grid .sched-left {
                gap: .3rem;
            }

            #merged-schedule-grid .teacher-dot {
                width: .5rem;
                height: .5rem;
            }
        }
    </style>

    <div class="w-full overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="min-w-[720px] w-full table-fixed border-collapse text-center text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="time-label bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">Horário</th>
                    @foreach ($weekdays as $dayId => $dayName)
                        <th class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($timePeriods->chunk(2) as $hourSlots)
                    @php
                        $hourSlots = $hourSlots->values();
                        $firstSlot = $hourSlots->first();
                        $secondSlot = $hourSlots->get(1);
                    @endphp
                    <tr>
                        <td class="time-label bg-gray-200 dark:bg-gray-700 border-b border-gray-300 font-bold text-gray-900 dark:text-gray-100">
                            {{ \Carbon\Carbon::parse($firstSlot->start_time)->format('H:i') }}
                        </td>
                        @foreach ($weekdays as $dayId => $dayName)
                            @php
                                $firstItems = collect($calendar[$firstSlot->id][$dayId] ?? [])
                                    ->sortBy(fn ($schedule) => $schedule->teacher?->name ?? '')->values();
                                $secondItems = $secondSlot
                                    ? collect($calendar[$secondSlot->id][$dayId] ?? [])
                                        ->sortBy(fn ($schedule) => $schedule->teacher?->name ?? '')->values()
                                    : collect();
                            @endphp
                            <td class="border-b border-gray-300 px-2 text-left align-top">
                                <div class="hour-cell">
                                    <div class="half-slot {{ ! $firstSlot->active ? 'bg-inactive-slot' : '' }}">
                                        <div class="lesson-block">
                                            @foreach ($firstItems as $schedule)
                                                @php
                                                    $teacher = $schedule->teacher;
                                                    $teacherLabel = $teacher?->acronym ?: $teacher?->number ?: $teacher?->name ?: 'Docente';
                                                    $color = $teacher ? ($teacherPalette[$teacher->id] ?? 'hsl(210 10% 50%)') : 'hsl(210 10% 50%)';
                                                    $subject = $schedule->subject?->acronym ?: $schedule->subject?->name ?: 'Disc.';
                                                    $room = $schedule->room?->name;
                                                @endphp
                                                <div class="sched-pill" style="background: {{ $color }}">
                                                    <span class="sched-left">
                                                        <span class="teacher-dot"></span>
                                                        <span class="sched-title" title="{{ $teacher?->name }} - {{ $subject }}{{ $room ? ' @ '.$room : '' }}">
                                                            {{ $teacherLabel }} - {{ $subject }}{{ $room ? ' @ '.$room : '' }}
                                                        </span>
                                                    </span>
                                                    <span class="text-[0.65rem] font-mono whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($schedule->timeperiod->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->timeperiod->end_time)->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="half-slot {{ $secondSlot && ! $secondSlot->active ? 'bg-inactive-slot' : '' }}">
                                        <div class="lesson-block">
                                            @foreach ($secondItems as $schedule)
                                                @php
                                                    $teacher = $schedule->teacher;
                                                    $teacherLabel = $teacher?->acronym ?: $teacher?->number ?: $teacher?->name ?: 'Docente';
                                                    $color = $teacher ? ($teacherPalette[$teacher->id] ?? 'hsl(210 10% 50%)') : 'hsl(210 10% 50%)';
                                                    $subject = $schedule->subject?->acronym ?: $schedule->subject?->name ?: 'Disc.';
                                                    $room = $schedule->room?->name;
                                                @endphp
                                                <div class="sched-pill" style="background: {{ $color }}">
                                                    <span class="sched-left">
                                                        <span class="teacher-dot"></span>
                                                        <span class="sched-title" title="{{ $teacher?->name }} - {{ $subject }}{{ $room ? ' @ '.$room : '' }}">
                                                            {{ $teacherLabel }} - {{ $subject }}{{ $room ? ' @ '.$room : '' }}
                                                        </span>
                                                    </span>
                                                    <span class="text-[0.65rem] font-mono whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($schedule->timeperiod->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->timeperiod->end_time)->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

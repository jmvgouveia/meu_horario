<div id="calendar-container" wire:poll.30s>
    <style>
        .bg-inactive-slot {
            background-color: #f3f4f6;
            color: #9ca3af;
            font-style: italic;
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            align-items: stretch;
        }

        .sched-pill {
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

        .sched-left {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: 0;
        }

        .teacher-dot {
            width: .65rem;
            height: .65rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, .75);
            flex: 0 0 auto;
        }

        .sched-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 24ch;
        }
    </style>

    <div class="w-full overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="min-w-[900px] w-full table-fixed border-collapse text-center text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 sticky left-0 z-10">Horário</th>
                    @foreach ($weekdays as $dayId => $dayName)
                    <th class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($timePeriods as $slot)
                @php
                $slotLabel = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                @endphp

                <tr>
                    <td class="sticky left-0 z-10 bg-gray-200 dark:bg-gray-700 border-b border-gray-300 text-gray-900 px-2 py-3 align-middle leading-tight">
                        <div class="flex items-center justify-center h-full font-bold text-sm">
                            {{ $slotLabel }}
                        </div>
                    </td>

                    @foreach ($weekdays as $dayId => $dayName)
                    @php
                    $items = collect($calendar[$slot->id][$dayId] ?? [])
                        ->sortBy(fn($schedule) => $schedule->teacher?->name ?? '')
                        ->values();
                    @endphp

                    <td class="border-b px-2 py-3 text-left align-top {{ ! $slot->active ? 'bg-inactive-slot' : '' }}">
                        @if ($items->isNotEmpty())
                        <div class="stack">
                            @foreach ($items as $schedule)
                            @php
                            $teacher = $schedule->teacher;
                            $teacherLabel = $teacher?->acronym ?: $teacher?->number ?: $teacher?->name ?: 'Docente';
                            $color = $teacher ? ($teacherPalette[$teacher->id] ?? 'hsl(210 10% 50%)') : 'hsl(210 10% 50%)';
                            $subject = $schedule->subject?->acronym ?: $schedule->subject?->name ?: 'Disc.';
                            $room = $schedule->room?->name;
                            @endphp

                            <div class="sched-pill" style="background: {{ $color }}">
                                <div class="sched-left">
                                    <span class="teacher-dot"></span>
                                    <span class="sched-title" title="{{ $teacher?->name }} - {{ $subject }}{{ $room ? ' @ ' . $room : '' }}">
                                        {{ $teacherLabel }} - {{ $subject }}{{ $room ? ' @ ' . $room : '' }}
                                    </span>
                                </div>
                                <span class="text-[0.65rem] opacity-90 font-mono whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($schedule->timeperiod->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->timeperiod->end_time)->format('H:i') }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        &nbsp;
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

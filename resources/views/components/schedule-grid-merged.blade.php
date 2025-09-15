<x-filament::page wire:poll.30s>

    <div id="calendar-container">
        <style>
            td[rowspan="2"] {
                vertical-align: top
            }

            .bg-inactive-slot {
                background-color: #f3f4f6;
                color: #9ca3af;
                font-style: italic
            }

            .stack {
                display: flex;
                flex-direction: column;
                gap: .35rem;
                align-items: stretch
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
                box-shadow: 0 1px 2px rgba(0, 0, 0, .08)
            }

            .sched-left {
                display: flex;
                align-items: center;
                gap: .5rem;
                min-width: 0
            }

            .teacher-dot {
                width: .65rem;
                height: .65rem;
                border-radius: 9999px;
                flex: 0 0 auto
            }

            .sched-title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 22ch
            }

            .plusmore {
                font-size: .70rem;
                opacity: .8
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
                    @php $ocupado = []; @endphp

                    @for ($i = 0; $i < count($timePeriods) - 1; $i++)
                        @php
                        $slot=$timePeriods[$i];
                        $nextSlot=$timePeriods[$i + 1];
                        $isSlot1=\Carbon\Carbon::parse($slot->start_time)->minute === 0;
                        $startHour = \Carbon\Carbon::parse($slot->start_time)->format('H:i');

                        $isSlot1Active = $slot->active;
                        $isSlot2Active = $nextSlot->active;
                        @endphp

                        @if ($isSlot1)
                        {{-- Linha 1 (slot :00) --}}
                        <tr>
                            <td class="sticky left-0 z-10 bg-gray-200 dark:bg-gray-700 border-b border-gray-300 text-gray-900 px-2 align-middle leading-tight"
                                rowspan="2" style="vertical-align: middle;">
                                <div class="flex items-center justify-center h-full font-bold text-sm min-h-[100%]">
                                    {{ $startHour }}
                                </div>
                            </td>

                            @foreach ($weekdays as $dayId => $dayName)
                            @if (!empty($ocupado[$i][$dayId]) || !empty($ocupado[$i + 1][$dayId]))
                            @continue
                            @endif

                            @php
                            $s1 = $calendar[$slot->id][$dayId] ?? [];
                            // só os que COMEÇAM nesta slot :00
                            $startsHere = collect($s1)->filter(fn($sch) => \Carbon\Carbon::parse($sch->timeperiod->start_time)->minute === 0)->values();
                            $count = $startsHere->count();
                            @endphp

                            @if ($count > 0)
                            @php
                            $ocupado[$i][$dayId] = true;
                            $ocupado[$i + 1][$dayId] = true;
                            $toShow = $startsHere->take(3);
                            $more = max(0, $count - 3);
                            @endphp
                            <td class="border-b px-2 py-3 text-left align-top" rowspan="2">
                                <div class="stack">
                                    @foreach ($toShow as $schedule)
                                    @php
                                    $t = $schedule->teacher;
                                    $color = $teacherPalette[$t->id] ?? 'hsl(210 10% 50%)';
                                    $disc = $schedule->subject->acronym ?? $schedule->subject->name ?? 'Disc.';
                                    $room = $schedule->room->name ?? null;
                                    @endphp
                                    <div class="sched-pill" style="background: {{ $color }}">
                                        <div class="sched-left">
                                            <span class="teacher-dot" style="background: {{ $color }}"></span>
                                            <span class="sched-title" title="{{ $t->name }} — {{ $disc }}{{ $room ? ' @ '.$room : '' }}">
                                                {{ $t->number }} — {{ $disc }}{{ $room ? ' @ '.$room : '' }}
                                            </span>
                                        </div>
                                        <span class="text-[0.65rem] opacity-90 font-mono">
                                            {{ \Carbon\Carbon::parse($schedule->timeperiod->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->timeperiod->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                    @endforeach
                                    @if ($more > 0)
                                    <div class="plusmore">+{{ $more }} mais…</div>
                                    @endif
                                </div>
                            </td>
                            @continue
                            @endif

                            <td class="border-b px-2 py-3 text-center align-top {{ !$isSlot1Active ? 'bg-inactive-slot' : '' }}">
                                &nbsp;
                            </td>
                            @endforeach
                        </tr>

                        {{-- Linha 2 (slot :30) --}}
                        <tr>
                            @foreach ($weekdays as $dayId => $dayName)
                            @if (!empty($ocupado[$i + 1][$dayId]))
                            @continue
                            @endif

                            @php
                            $s2 = $calendar[$nextSlot->id][$dayId] ?? [];
                            // só os que COMEÇAM nesta slot :30
                            $startsHere = collect($s2)->filter(fn($sch) => \Carbon\Carbon::parse($sch->timeperiod->start_time)->minute === 30)->values();
                            $count = $startsHere->count();
                            @endphp

                            @if ($count > 0)
                            @php
                            $ocupado[$i + 1][$dayId] = true;
                            $ocupado[$i + 2][$dayId] = true;
                            $toShow = $startsHere->take(3);
                            $more = max(0, $count - 3);
                            @endphp
                            <td class="border-b px-2 py-3 text-left align-top" rowspan="2">
                                <div class="stack">
                                    @foreach ($toShow as $schedule)
                                    @php
                                    $t = $schedule->teacher;
                                    $color = $teacherPalette[$t->id] ?? 'hsl(210 10% 50%)';
                                    $disc = $schedule->subject->acronym ?? $schedule->subject->name ?? 'Disc.';

                                    $room = $schedule->room->name ?? null;
                                    @endphp
                                    <div class="sched-pill" style="background: {{ $color }}">
                                        <div class="sched-left">
                                            <span class="teacher-dot" style="background: {{ $color }}"></span>
                                            <span class="sched-title" title="{{ $t->name }} — {{ $disc }}{{ $room ? ' @ '.$room : '' }}">
                                                {{ $t->acronym }} — {{ $disc }}{{ $room ? ' @ '.$room : '' }}
                                            </span>
                                        </div>
                                        <span class="text-[0.65rem] opacity-90 font-mono">
                                            {{ \Carbon\Carbon::parse($schedule->timeperiod->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->timeperiod->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                    @endforeach
                                    @if ($more > 0)
                                    <div class="plusmore">+{{ $more }} mais…</div>
                                    @endif
                                </div>
                            </td>
                            @continue
                            @endif

                            <td class="border-b px-2 py-3 text-center align-top {{ !$isSlot2Active ? 'bg-inactive-slot' : '' }}">
                                &nbsp;
                            </td>
                            @endforeach
                        </tr>
                        @endif
                        @endfor
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
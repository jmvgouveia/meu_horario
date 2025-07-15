<style>
    .notificacao {
        position: absolute;
        top: -4px;
        right: -4px;
        height: 16px;
        width: 16px;
        background-color: #dc2626;
        /* padrão: vermelho */
        border-radius: 9999px;
        z-index: 10;
        color: white;
        font-size: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;

        background: radial-gradient(circle at 30% 30%, #ffffff44, transparent 70%),
            var(--notif-color, #dc2626);
        box-shadow:
            inset -1px -1px 2px rgba(0, 0, 0, 0.2),
            1px 1px 2px rgba(0, 0, 0, 0.3);
        transition: transform 0.2s;
    }

    .notificacao.dp {
        background-color: #065f46;
        color: white;
        font-size: 9px;
        font-weight: bold;
        height: 18px;
        width: 18px;
        top: -6px;
        right: -6px;
    }

    .notificacao.pulsar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 9999px;
        opacity: 0.6;
        animation: pulse 1.2s infinite ease-in-out;
        z-index: -1;
    }

    .notificacao.T {
        background-color: #2563eb;
        /* azul */
    }

    .notificacao.R {
        background-color: #dc2626;
        /* vermelho */
    }

    .notificacao.E {
        background-color: #7c3aed;
        /* laranja */
    }

    .notificacao.pulsar.T::after {
        background-color: #2563eb;
    }

    .notificacao.pulsar.R::after {
        background-color: #dc2626;
    }

    .notificacao.pulsar.E::after {
        background-color: #7c3aed;
    }

    .status-badge {
        display: block;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        color: white;
        max-width: 100%;
        white-space: normal;
        line-height: 1.3;
        overflow: hidden;
        font-weight: 500;
        font-size: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 1px rgba(0, 0, 0, 0.06);
    }


    td[rowspan="2"] {
        vertical-align: top;
        /* ou middle */
    }


    .status-title {
        font-weight: 700;
        font-size: 0.875rem;
    }

    .status-info {
        font-size: 9px;
    }

    .badge-aprovado {
        background-color: #059669;
    }

    .badge-pendente {
        background-color: #ca8a04;
    }

    .badge-reuniao-tee {
        background-color: #1e40af;
        color: white;
    }

    .badge-rejeitado {
        background-color: #dc2626;
    }

    .bg-inactive-slot {
        background-color: #f3f4f6;
        /* gray-100 */
        color: #9ca3af;
        /* gray-400 */
        font-style: italic;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.6;
        }

        70% {
            transform: scale(2.5);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 0;
        }
    }
</style>
<div id="calendar-container">
    <div class="flex items-center justify-end text-xs text-gray-500 mt-2 space-x-1" id="last-updated">
    </div>
    <div class="w-full overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="min-w-[800px] w-full table-fixed border-collapse text-center text-sm">
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
                    @endphp

                    @if ($isSlot1)
                    {{-- Linha 1: Slot1 --}}
                    <tr>
                        <td class="sticky left-0 z-10 bg-gray-200 dark:bg-gray-700 border-b border-gray-300 text-gray-900 px-2 align-middle leading-tight" rowspan="2" style="vertical-align: middle;">
                            <div class="flex items-center justify-center h-full font-bold text-sm min-h-[100%]">
                                {{ $startHour }}

                                <!-- <br> -->
                                <!-- {{ $slot->id . ' - ' . $nextSlot->id }} -->
                            </div>
                        </td>

                        @php
                        $isSlot1Active = $slot->active;
                        $isSlot2Active = $nextSlot->active;
                        @endphp
                        @foreach ($weekdays as $dayId=> $dayName)

                        {{-- Se já foi marcado por um rowspan anterior --}}
                        @if (!empty($ocupado[$i][$dayId]) || !empty($ocupado[$i + 1][$dayId]))
                        @continue
                        @endif

                        @php
                        $s1 = $calendar[$slot->id][$dayId] ?? [];
                        $s2 = $calendar[$nextSlot->id][$dayId] ?? [];
                        @endphp

                        {{-- Caso 1: Começa às :00 na slot atual --}}
                        @if (!empty($s1))
                        @php
                        $schedule = $s1[0];
                        $startMin = \Carbon\Carbon::parse($schedule->timeperiod->start_time)->minute;
                        @endphp

                        @if ($startMin === 0)
                        @php
                        $ocupado[$i][$dayId] = true;
                        $ocupado[$i + 1][$dayId] = true;
                        @endphp
                        <td class="border-b px-2 py-3 text-center align-top" rowspan="2">
                            @include('components.schedule-badge', compact('schedule', 'recusados', 'PedidosAprovadosDP', 'escalados'))
                        </td>
                        @continue
                        @endif
                        @endif

                        {{-- Nenhuma marcação agora; "+" na linha 1 --}}
                        @if (! $isSlot1Active)
                        <td class="border-b px-2 py-3 text-center align-top bg-inactive-slot italic text-gray-400">
                            +
                        </td>
                        @else
                        <td class="border-b px-2 py-3 text-center align-top">
                            <a href="{{ route('filament.admin.resources.schedules.create', ['weekday' => $dayId, 'timeperiod' => $slot->id]) }}" class="text-blue-500 text-lg">
                                +
                            </a>
                        </td>
                        @endif
                        @endforeach
                    </tr>

                    {{-- Linha 2: Slot2 --}}
                    <tr>
                        @foreach ($weekdays as $dayId => $dayName)
                        @if (!empty($ocupado[$i + 1][$dayId]))
                        @continue
                        @endif

                        @php
                        $s2 = $calendar[$nextSlot->id][$dayId] ?? [];
                        @endphp

                        {{-- Caso 2: Começa às :30 na slot seguinte --}}
                        @if (!empty($s2))
                        @php
                        $schedule = $s2[0];
                        $startMin = \Carbon\Carbon::parse($schedule->timeperiod->start_time)->minute;
                        @endphp

                        @if ($startMin === 30)
                        @php
                        $ocupado[$i + 1][$dayId] = true;
                        $ocupado[$i + 2][$dayId] = true;
                        @endphp
                        <td class="border-b px-2 py-3 text-center align-top" rowspan="2">
                            @include('components.schedule-badge', compact('schedule', 'recusados', 'PedidosAprovadosDP', 'escalados'))
                        </td>
                        @continue
                        @endif
                        @endif

                        @if (! $isSlot2Active)
                        <td class="border-b px-2 py-3 text-center align-top bg-inactive-slot italic text-gray-400">
                            +
                        </td>
                        @else
                        <td class="border-b px-2 py-3 text-center align-top">
                            <a href="{{ route('filament.admin.resources.schedules.create', ['weekday' => $dayId, 'timeperiod' => $nextSlot->id]) }}" class="text-blue-500 text-lg">
                                +
                            </a>
                        </td>
                        @endif
                        @endforeach
                    </tr>
                    @endif
                    @endfor
            </tbody>







        </table>
        <br>
        <div class="flex flex-wrap justify-center gap-2 mb-4 px-4 text-xs font-medium max-w-4xl mx-auto">
            <div class="status-badge badge-reuniao-tee w-28 text-center truncate">Não Letiva</div>
            <div class="status-badge badge-aprovado w-28 text-center truncate">Aprovado</div>
            <div class="status-badge badge-pendente w-28 text-center truncate">Pendente</div>

            <div class="flex items-center gap-1 w-36 truncate">
                <span class="notificacao T pulsar" style="position: static; transform: scale(0.75);">T</span>

                <span>Pedido de Troca</span>
            </div>

            <div class="flex items-center gap-1 w-36 truncate">
                <span class="notificacao R pulsar" style="position: static; transform: scale(0.75);">R</span>
                <span>Pedido Recusado</span>
            </div>

            <div class="flex items-center gap-1 w-36 truncate">
                <span class="notificacao E pulsar" style="position: static; transform: scale(0.75);">E</span>
                <span>Pedido Escalado</span>
            </div>

            <div class="flex items-center gap-1 w-36 truncate">
                <span class="notificacao dp" style="position: static; transform: scale(0.75);">DP</span>
                <span>Troca Aprovada por DP</span>
            </div>
        </div>
    </div>
</div>

<script>
    setInterval(function() {
        fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newCalendar = doc.querySelector('#calendar-container');
                const newTimestamp = doc.querySelector('#last-updated');

                if (newCalendar && newTimestamp) {
                    document.querySelector('#calendar-container').innerHTML = newCalendar.innerHTML;
                    document.querySelector('#last-updated').innerHTML = newTimestamp.innerHTML;
                }
            })
            .catch(console.error);
    }, 5000);
</script>
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

        <!-- <span> Atualizado: {{ now()->format('d/m/Y H:i:s') }} </span> -->


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

                @foreach ($timePeriods as $timePeriod)
                <tr>
                    <td class="px-4 py-3 font-semibold bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 sticky left-0 z-10 whitespace-nowrap">
                        {{ $timePeriod->description }}
                    </td>

                    @foreach ($weekdays as $dayId => $dayName)
                    @php $schedulesInSlot = $calendar[$timePeriod->id][$dayId] ?? []; @endphp

                    <td class="px-4 py-3 align-top text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-700">
                        @forelse ($schedulesInSlot as $schedule)
                        @php
                        $badgeClass = match($schedule->status) {
                        'Aprovado', 'Aprovado DP' => 'badge-aprovado',
                        'Pendente' => 'badge-pendente',
                        'Rejeitado' => 'badge-rejeitado',
                        default => '',
                        };

                        $info = ['Sala: ' . ($schedule->room->name ?? '—')];

                        if (!empty($schedule->classes)) {
                        $info[] = collect($schedule->classes)->pluck('name')->join(', ');
                        }

                        if (!empty($schedule->shift)) {
                        $info[] = 'Turno: ' . $schedule->shift;
                        }

                        $info[] = 'ID: ' . $schedule->id;

                        $hasNotification = false;
                        $notifLetter = '';
                        $notifClass = '';
                        $tooltip = '';
                        $link = route('filament.admin.resources.schedules.edit', $schedule->id);

                        $authId = auth()->user()?->teacher?->id;

                        // Recusado
                        if ($recusados->has($schedule->id)) {
                        $hasNotification = true;
                        $notifLetter = 'R';
                        $tooltip = 'Pedido de troca recusado';
                        $link = route('filament.admin.resources.schedule-requests.edit', $recusados[$schedule->id]->id);
                        }

                        // Aprovado pelo DP
                        if ($schedule->status === 'Aprovado DP' && $PedidosAprovadosDP->has($schedule->id)) {
                        $req = $PedidosAprovadosDP[$schedule->id];
                        $hasNotification = true;
                        $notifLetter = 'DP';
                        $notifClass = 'dp';
                        $tooltip = 'Troca aprovada';
                        $link = route('filament.admin.resources.schedule-requests.edit', $req->id);
                        }

                        // Escalado
                        if ($escalados->has($schedule->id)) {
                        $hasNotification = true;
                        $notifLetter = 'E';
                        $tooltip = 'Troca escalada';
                        $link = route('filament.admin.resources.schedule-conflicts.edit', $escalados[$schedule->id]->id);
                        }

                        // Novo: só considera pedidos com status relevante (Pendente, Escalado, Aprovado DP)
                        $firstRequest = $schedule->requests()
                        ->with('scheduleConflict.teacher.user')
                        ->whereIn('status', ['Pendente', 'Escalado', 'Aprovado DP'])
                        ->orderBy('created_at')
                        ->first();


                        if ($firstRequest && $firstRequest->status === 'Pendente') {
                        if ($authId === $firstRequest->scheduleConflict?->teacher?->id) {
                        $hasNotification = true;
                        $notifLetter = 'T';
                        $tooltip = 'Pedido pendente';
                        $link = route('filament.admin.resources.schedule-requests.edit', $firstRequest->id);
                        }
                        }
                        @endphp

                        @unless($schedule->status === 'Eliminado' || $schedule->status === 'Recusado DP')
                        <a href="{{ $link }}">
                            <div class="relative mb-2">

                                <div class="status-badge {{ in_array(strtolower($schedule->subject->name ?? ''), ['reunião', 'tee']) ? 'badge-reuniao-tee' : $badgeClass }}">
                                    <div class="status-title">{{ $schedule->subject->name ?? 'Sem Matéria' }}</div>
                                    @foreach ($info as $i)
                                    <div class="status-info">{{ $i }}</div>
                                    @endforeach
                                </div>




                                @if ($hasNotification)
                                <span class="notificacao {{ $notifLetter }} {{ $notifClass ?: 'pulsar' }}" title="{{ $tooltip }}">{{ $notifLetter }}</span>

                                @endif


                            </div>
                        </a>
                        @endunless
                        @empty
                        <a href="{{ route('filament.admin.resources.schedules.create', ['weekday' => $dayId, 'timeperiod' => $timePeriod->id]) }}" class="block p-2 text-gray-400 dark:text-gray-600 hover:text-blue-600 dark:hover:text-blue-400 transition">
                            +
                        </a>
                        @endforelse
                    </td>
                    @endforeach
                </tr>
                @endforeach

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

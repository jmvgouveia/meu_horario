@php
$badgeClass = match($schedule->status) {
'Aprovado', 'Aprovado DP' => 'badge-aprovado',
'Pendente' => 'badge-pendente',
'Rejeitado' => 'badge-rejeitado',
default => '',
};

$info = ['Sala: ' . ($schedule->room->name ?? '—')];

$displayShift = $schedule->shift;

if (blank($displayShift)) {
    $studentNumbers = $schedule->relationLoaded('students')
        ? $schedule->students->pluck('number')
        : $schedule->students()->pluck('students.number');

    if ($studentNumbers->isNotEmpty()) {
        $displayShift = $studentNumbers->sort()->implode(', ');
    }
}

if (!empty($schedule->classes)) {
$info[] = collect($schedule->classes)->pluck('name')->join(', ');
}

if (!empty($displayShift)) {
    $info[] = 'Turno: ' . $displayShift;
}

$info[] = 'ID: ' . $schedule->id;

$hasNotification = false;
$notifLetter = '';
$notifClass = '';
$tooltip = '';
$link = route('filament.admin.resources.schedules.edit', $schedule->id);

$authId = auth()->user()?->teacher?->id;

if ($recusados->has($schedule->id)) {
$hasNotification = true;
$notifLetter = 'R';
$tooltip = 'Pedido de troca recusado';
$link = route('filament.admin.resources.schedule-requests.edit', $recusados[$schedule->id]->id);
}

if ($schedule->status === 'Aprovado DP' && $PedidosAprovadosDP->has($schedule->id)) {
$hasNotification = true;
$notifLetter = 'DP';
$notifClass = 'dp';
$tooltip = 'Troca aprovada pelo DP';
}

if ($escalados->has($schedule->id)) {
$hasNotification = true;
$notifLetter = 'E';
$tooltip = 'Troca escalada';
$link = route('filament.admin.resources.schedule-conflicts.edit', $escalados[$schedule->id]->id);
}

$firstRequest = \App\Models\ScheduleRequest::query()
->where(function ($query) use ($schedule) {
    $query->where('id_schedule', $schedule->id)
        ->orWhere('id_new_schedule', $schedule->id);
})
->with('scheduleConflict.teacher.user')
->whereIn('status', ['Pendente', 'Escalado', 'Aprovado DP'])
->orderBy('created_at')
->first();

if ($firstRequest && $firstRequest->status === 'Pendente') {
if ($authId === $firstRequest->scheduleConflict?->teacher?->id
    || $authId === $firstRequest->id_teacher_requester) {
$hasNotification = true;
$notifLetter = 'T';
$tooltip = 'Pedido pendente';
$link = route('filament.admin.resources.schedule-requests.edit', $firstRequest->id);
}
}
@endphp

<a href="{{ $link }}">
    <div class="relative mb-2">
        <div class="status-badge {{ in_array(strtolower($schedule->subject->name ?? ''), ['reunião', 'tee']) ? 'badge-reuniao-tee' : $badgeClass }}">
            <div class="status-title">{{ $schedule->subject->name ?? 'Sem Matéria' }}</div>
            @foreach ($info as $i)
            <div class="status-info">{{ $i }}</div>
            @endforeach
        </div>

        @if ($hasNotification)
        <span class="notificacao {{ $notifLetter }} {{ $notifClass ?: 'pulsar' }}" title="{{ $tooltip }}">
            {{ $notifLetter }}
        </span>
        @endif
    </div>
</a>

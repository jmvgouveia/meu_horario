<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use App\Models\ScheduleRequest;
use App\Models\Teacher;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class ListScheduleRequests extends ListRecords
{
    protected static string $resource = ScheduleRequestResource::class;

    public string $filtroAtual = 'recebidos';

    public function mount(): void
    {
        parent::mount();

        if ($this->isGestorConflitos()) {
            $this->filtroAtual = 'todos';
        }
    }

    protected function isGestorConflitos(): bool
    {
        return in_array(Filament::auth()->id(), [1]);
    }

    protected function getCounts(): array
    {
        if ($this->isGestorConflitos()) {
            return [
                'todos' => ScheduleRequest::count(),
                'meus' => 0,
                'recebidos' => 0,
            ];
        }

        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return ['todos' => 0, 'meus' => 0, 'recebidos' => 0];
        }

        $meus = ScheduleRequest::where('id_teacher', $teacher->id)->count();

        $recebidos = ScheduleRequest::whereHas('scheduleConflict', function ($q) use ($teacher) {
            $q->where('id_teacher', $teacher->id);
        })->where('status', '!=', 'Cancelado')->count();

        $todos = ScheduleRequest::where(function ($query) use ($teacher) {
            $query->where('id_teacher', $teacher->id)
                ->orWhere(function ($sub) use ($teacher) {
                    $sub->whereHas('scheduleConflict', fn($conf) => $conf->where('id_teacher', $teacher->id))
                        ->where('status', '!=', 'Cancelado');
                });
        })->count();

        return [
            'todos' => $todos,
            'meus' => $meus,
            'recebidos' => $recebidos,
        ];
    }

    protected function getHeaderActions(): array
    {
        if ($this->isGestorConflitos()) {
            return [];
        }

        $counts = $this->getCounts();

        return [
            Action::make('recebidos')
                ->label("Pedidos Recebidos ({$counts['recebidos']})")
                ->action(fn() => $this->filtroAtual = 'recebidos')
                ->color(fn() => $this->filtroAtual === 'recebidos' ? 'success' : 'gray'),
            Action::make('meus')
                ->label("Meus Pedidos ({$counts['meus']})")
                ->action(fn() => $this->filtroAtual = 'meus')
                ->color(fn() => $this->filtroAtual === 'meus' ? 'primary' : 'gray'),


        ];
    }

    protected function getTableQuery(): ?Builder
    {
        if ($this->isGestorConflitos()) {
            return ScheduleRequest::query();
        }

        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return ScheduleRequest::query()->whereRaw('0 = 1');
        }

        return match ($this->filtroAtual) {
            'meus' => ScheduleRequest::query()
                ->where('id_teacher', $teacher->id),

            'recebidos' => ScheduleRequest::query()
                ->whereHas('scheduleConflict', fn($q) => $q->where('id_teacher', $teacher->id))
                ->where('status', '!=', 'Cancelado'),

            default => ScheduleRequest::query()
                ->where(function ($q) use ($teacher) {
                    $q->where('id_teacher', $teacher->id)
                        ->orWhere(function ($sub) use ($teacher) {
                            $sub->whereHas('scheduleConflict', fn($conf) => $conf->where('id_teacher', $teacher->id))
                                ->where('status', '!=', 'Cancelado');
                        });
                }),
        };
    }

    protected function getCurrentTeacher(): ?Teacher
    {
        return Teacher::where('id_user', Filament::auth()->id())->first();
    }
}

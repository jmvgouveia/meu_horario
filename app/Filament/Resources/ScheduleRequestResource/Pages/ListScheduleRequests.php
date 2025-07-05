<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class ListScheduleRequests extends ListRecords
{
    protected static string $resource = ScheduleRequestResource::class;

    public string $filtroAtual = 'recebidos';

    // public function mount(): void
    // {
    //     parent::mount();

    //     if ($this->isGestorConflitos()) {
    //         $this->filtroAtual = 'todos';
    //     }
    // }

    protected function isGestorConflitos(): bool
    {
        return in_array(Filament::auth()->id(), [1]);
    }

    protected function getCounts(): array
    {
        $teacher = $this->getCurrentTeacher(); // ou o que usares

        if (!$teacher) {
            return ['meus' => 0, 'recebidos' => 0];
        }

        $schoolYearId = SchoolYear::where('active', true)->value('id');

        if (!$schoolYearId) {
            return ['meus' => 0, 'recebidos' => 0];
        }

        // Meus pedidos
        $meus = ScheduleRequest::where('id_teacher', $teacher->id)
            ->where(function ($query) use ($schoolYearId) {
                $query

                    ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $schoolYearId))
                    ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $schoolYearId));
            })
            ->where('status', '!=', 'Escalado')
            ->where('status', '!=', 'Eliminado')
            ->count();

        // Pedidos recebidos
        $recebidos = ScheduleRequest::whereHas('scheduleConflict', function ($q) use ($teacher) {
            $q->where('id_teacher', $teacher->id);
        })
            ->where('status', '!=', 'Eliminado')
            ->where('status', '!=', 'Escalado')
            ->where(function ($query) use ($schoolYearId) {
                $query
                    ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $schoolYearId))
                    ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $schoolYearId));
            })
            ->count();

        return compact('meus', 'recebidos');
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
            return ScheduleRequest::query()
                ->where(function ($query) {
                    $query
                        ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()))
                        ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()));
                });
        }

        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return ScheduleRequest::query()->whereRaw('0 = 1');
        }

        return match ($this->filtroAtual) {
            'meus' => ScheduleRequest::query()
                ->where('id_teacher', $teacher->id)
                ->where('status', '!=', 'Escalado')
                // ->where('status', '!=', 'Eliminado')
                ->where(function ($query) {
                    $query
                        ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()))
                        ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()));
                }),

            'recebidos' => ScheduleRequest::query()
                ->whereHas('scheduleConflict', fn($q) => $q->where('id_teacher', $teacher->id))
                ->where('status', '!=', 'Escalado')
                // ->where('status', '!=', 'Eliminado')
                ->where(function ($query) {
                    $query
                        ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()))
                        ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()));
                }),

            default => ScheduleRequest::query()
                ->where(function ($q) use ($teacher) {
                    $q->where('id_teacher', $teacher->id)
                        ->orWhere(function ($sub) use ($teacher) {
                            $sub->whereHas('scheduleConflict', fn($conf) => $conf->where('id_teacher', $teacher->id))
                                ->where('status', '!=', 'Cancelado');
                        });
                })
                ->where(function ($query) {
                    $query
                        ->whereHas('scheduleNew', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()))
                        ->orWhereHas('scheduleConflict', fn($q) => $q->where('id_schoolyear', $this->getActiveSchoolYearId()));
                }),
        };
    }

    protected function getActiveSchoolYearId(): ?int
    {
        return \App\Models\SchoolYear::where('active', true)->value('id');
    }

    protected function getCurrentTeacher(): ?Teacher
    {
        return Teacher::where('id_user', Filament::auth()->id())->first();
    }
}

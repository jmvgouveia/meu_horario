<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\ScheduleResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\ScheduleResource\Traits\HandlesScheduleSwap;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Filament\Actions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    use InteractsWithActions, CheckScheduleWindow, ChecksScheduleConflicts, HandlesScheduleSwap;

    protected $listeners = ['botaoSolicitarTrocaClicado' => 'onSolicitarTrocaClicado'];
    public ?string $justification = null;

    //  Armazena estado interno da página
    public ?Schedule $conflictingSchedule = null;

    //preencher automaticamente ano letivo e professor
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $activeYear = SchoolYear::where('active', true)->first();
        if ($activeYear) {
            $data['id_schoolyear'] = $activeYear->id;
        }

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        if ($teacher) {
            $data['id_teacher'] = $teacher->id;
        }

        $data['status'] = 'Aprovado';
        return $data;
    }

    protected function beforeCreate(): void
    {
        $this->validateScheduleWindow();
        $this->checkScheduleConflictsAndAvailability($this->data);
    }

    protected function beforeSave(): void
    {
        $this->validateScheduleWindow();
        $this->checkScheduleConflictsAndAvailability($this->data, $this->record->id);
    }

    protected function afterCreate(): void
    {
        $this->afterSave();
        ScheduleResource::hoursCounterUpdate($this->record, false);
    }

    public function mount(): void
    {
        $this->form->fill([
            'id_weekday' => request('weekday'),
            'id_timeperiod' => request('timeperiod'),
        ]);
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        //     // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);
        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }
}

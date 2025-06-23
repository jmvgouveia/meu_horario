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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;


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

        try {

            DB::transaction(function () {
                $this->validateScheduleWindow();
                $this->checkScheduleConflictsAndAvailability($this->data);
            });
        } catch (\Exception $e) {

            Notification::make()
                ->title('Erro ao criar o horário')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e; // Re-throw the exception to prevent saving
        }
    }

    protected function beforeSave(): void
    {
        $this->validateScheduleWindow();
        $this->checkScheduleConflictsAndAvailability($this->data, $this->record->id);
    }

    protected function afterCreate(): void
    {

        try {
            DB::transaction(function () {
                //$this->afterSave();
                $this->record->classes()->sync($this->data['id_classes'] ?? []);
                $this->record->students()->sync($this->data['students'] ?? []);
                ScheduleResource::hoursCounterUpdate($this->record, false);
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao criar o horário')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e; // Re-throw the exception to prevent saving
        }
    }

    public function mount(): void
    {
        $this->form->fill([
            'id_weekday' => request('weekday'),
            'id_timeperiod' => request('timeperiod'),
        ]);
    }


    // ADICIONADO NO BEFORE SAVE
    // protected function afterSave(): void 
    // {
    //     $record = $this->record;
    //     //     // Sincroniza as turmas (many-to-many)
    //     $record->classes()->sync($this->data['id_classes'] ?? []);
    //     // Sincroniza os alunos (many-to-many)
    //     $record->students()->sync($this->data['students'] ?? []);
    // }
}

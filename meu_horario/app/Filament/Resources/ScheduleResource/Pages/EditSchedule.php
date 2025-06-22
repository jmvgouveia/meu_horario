<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\ScheduleResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\ScheduleResource\Traits\HandlesScheduleSwap;
use App\Models\Schedule;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    public ?Schedule $conflictingSchedule = null;

    use CheckScheduleWindow, ChecksScheduleConflicts, HandlesScheduleSwap;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function beforeSave(): void
    {
        $this->validateScheduleWindow();
        $this->checkScheduleConflictsAndAvailability($this->data, $this->record?->id);


        $this->form->fill([
            'status' => 'Aprovado',
        ]);
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Sincroniza as turmas (many-to-many)
        $record->classes()->sync($this->data['id_classes'] ?? []);

        // Sincroniza os alunos (many-to-many)
        $record->students()->sync($this->data['students'] ?? []);
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),


            DeleteAction::make()
                ->label('Eliminar Horário')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {

                    $this->validateScheduleWindow();

                    ScheduleResource::rollbackScheduleRequest($this->record);

                    if ($this->record->status !== 'Pendente') {

                        ScheduleResource::hoursCounterUpdate($this->record, true);
                    }

                    $this->record->delete();

                    Notification::make()
                        ->title("Horário Eliminado")
                        ->body("O horário com ID: {$this->record->id} foi eliminado com sucesso.")
                        ->success()
                        ->sendToDatabase(Filament::auth()->user());

                    Notification::make()
                        ->title('Horário Eliminado')
                        ->body("O horário com ID: {$this->record->id} foi eliminado com sucesso.")
                        ->success()
                        ->send();
                    $this->redirect(filament()->getUrl());
                }),

            $this->getCancelFormAction(),
        ];
    }

    protected function getRecordActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('justificarConflito')
                    ->label('Solicitar Troca de Horário')
                    ->visible(fn($livewire) => $livewire->conflictingSchedule !== null)
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->modalHeading('Justificação do Conflito')
                    ->modalSubmitActionLabel('Submeter Justificação')
                    ->modalCancelActionLabel('Cancelar')
                    ->form([
                        Textarea::make('justification')
                            ->label('Escreva a justificação')
                            ->helperText('Escreva uma justificação para o conflito de horário. Esta justificação será enviada ao professor responsável pelo horário em conflito.')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(function (array $data, $livewire) {
                        $livewire->submitJustification($data);
                    })
            ]),
        ];
    }

    /* protected function getRedirectUrl(): string
    {
        return ScheduleResource::getUrl();
    } */
}

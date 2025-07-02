<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\ScheduleResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\ScheduleResource\Traits\HandlesScheduleSwap;
use App\Models\Schedule;
use App\Models\ScheduleRequest;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

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
        try {
            DB::transaction(function () {
                $this->validateScheduleWindow();
                $this->checkScheduleConflictsAndAvailability($this->data, $this->record?->id);
                $this->form->fill([
                    'status' => 'Aprovado',
                ]);
            });
        } catch (\Exception $e) {

            Notification::make()
                ->title('Erro ao salvar o horário')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function afterSave(): void
    {
        try {
            DB::transaction(function () {
                $record = $this->record;

                $record->classes()->sync($this->data['id_classes'] ?? []);

                $record->students()->sync($this->data['students'] ?? []);
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao atualizar o horário')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    public function getFormActions(): array
    {
        $record = $this->record;

        return [


            $this->getSaveFormAction(),

            DeleteAction::make()
                ->label('Eliminar Horário')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Horário')
                ->modalDescription('Ao eliminar este horário, qualquer pedido de troca que estava pendente com este registo será automaticamente aprovado.')
                ->action(function () {
                    try {

                        DB::transaction(function () {
                            $record = $this->record;

                            $pendingRequest = ScheduleRequest::where('id_schedule', $record->id)
                                ->where('status', 'Recusado')
                                ->first();

                            if ($pendingRequest) {
                                $scheduleNovo = $pendingRequest->scheduleNew;

                                if ($scheduleNovo) {
                                    $scheduleNovo->status = 'Aprovado';
                                    $scheduleNovo->save();

                                    ScheduleResource::hoursCounterUpdate($scheduleNovo, false);

                                    $pendingRequest->status = 'Aprovado';
                                    $pendingRequest->save();

                                    $requerente = $pendingRequest->requester?->user;
                                    $idNovo = $scheduleNovo->id;
                                    $idApagado = $record->id;

                                    if ($requerente) {
                                        Notification::make()
                                            ->title("Pedido de troca aprovado automaticamente")
                                            ->body("O horário em conflito (ID: {$idApagado}) foi eliminado. O seu horário (ID: {$idNovo}) foi aprovado automaticamente.")
                                            ->success()
                                            ->sendToDatabase($requerente);
                                    }
                                }
                            }

                            if ($record->status !== 'Pendente') {
                                ScheduleResource::hoursCounterUpdate($record, true);
                            }

                            ScheduleResource::rollbackScheduleRequest($this->record);
                            $record->delete();

                            Notification::make()
                                ->title("Horário Eliminado")
                                ->body("O horário com ID: {$record->id} foi eliminado com sucesso.")
                                ->success()
                                ->sendToDatabase(Filament::auth()->user());

                            $this->redirect(filament()->getUrl());
                        });
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao eliminar o horário')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
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
}

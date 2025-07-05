<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\CheckScheduleWindow;
use App\Filament\Resources\ScheduleResource\Traits\ChecksScheduleConflicts;
use App\Filament\Resources\ScheduleResource\Traits\HourCounter;
use App\Models\Room;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;

    use CheckScheduleWindow, HourCounter;

    protected function getFormActions(): array
    {
        $this->record->loadMissing('scheduleConflict');

        $teacherId = Filament::auth()->user()?->teacher?->id;
        $requesterId = $this->record->id_teacher;
        $conflictOwnerId = $this->record->scheduleConflict?->id_teacher;

        $isRequestOwner = $teacherId === $requesterId;
        $isReceiver = $teacherId === $conflictOwnerId;
        $isGestor = in_array(Filament::auth()->id(), [1]);

        $status = $this->record->status;
        $actions = [];

        // Aprovar troca
        if (($isReceiver || $isGestor) && $status !== 'Recusado' && $status !== 'Aprovado DP' && $status !== 'Aprovado') {
            $actions[] = Action::make('accept')
                ->label('Aceitar Troca')
                ->color('success')

                ->mountUsing(function () {
                    if ($this->record->status === 'Eliminado') {
                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        //return Action::halt();
                        //throw new Halt('Pedido já eliminado');
                        // Previne a abertura do modal
                        redirect(request()->header('Referer') ?? url()->previous() ?? filament()->getUrl());
                    }
                })
                ->visible(fn() => $this->record->status !== 'Eliminado')
                ->form([

                    Select::make('id_room_novo')
                        ->label('Sala Nova')
                        ->required()
                        ->options(fn() => $this->getAvailableRooms()),
                    Textarea::make('response')
                        ->label($isGestor ? 'Justificação da Aprovação (Gestor)' : 'Justificação da Aceitação')
                        ->required(),
                ])

                ->action(function (array $data) use ($isGestor) {


                    if ($this->record->status === 'Eliminado') {

                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido já foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        return redirect(filament()->getUrl());
                    }

                    DB::transaction(function () use ($data, $isGestor) {
                        $this->validateScheduleWindow();

                        $this->record->update([
                            'status' => $isGestor ? 'Aprovado DP' : 'Aprovado',
                            'response' => $data['response'],
                        ]);

                        $this->record->scheduleConflict?->update([
                            'status' => $isGestor ? 'Aprovado DP' : 'Aprovado',
                            'id_room' => $data['id_room_novo'],
                            'responded_at' => now(),
                        ]);

                        $this->record->scheduleNew?->update(['status' => 'Aprovado']);

                        $this->hoursCounterUpdate($this->record->scheduleNew, false);

                        extract($this->getScheduleDetails());

                        Notification::make()
                            ->title('Pedido de troca aprovado com sucesso')
                            ->body("Aprovou o pedido de {$requestername} para trocar a sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->success()
                            ->send();

                        Notification::make()
                            ->title("Pedido aprovado")
                            ->body("O professor {$ownername} aprovou o seu pedido para a troca da aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->success()
                            ->actions([
                                NotificationAction::make('Ver Pedido')
                                    ->url(route('filament.admin.resources.schedule-requests.edit', [
                                        'record' => $this->record->getKey(),
                                    ]))
                            ])
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Recusar troca
        if (($isReceiver || $isGestor) && $status !== 'Aprovado' && $status !== 'Aprovado DP' && $status !== 'Recusado') {
            $actions[] = Action::make('reject')
                ->label('Recusar Troca')
                ->color('danger')
                ->visible(fn() => $this->record->status !== 'Eliminado')
                ->mountUsing(function () {
                    if ($this->record->status === 'Eliminado') {
                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        // Previne a abertura do modal
                        redirect(request()->header('Referer') ?? url()->previous() ?? filament()->getUrl());
                    }
                })
                ->form([
                    Textarea::make('response')->label('Justificação para Recusa')->required(),
                ])
                ->action(function (array $data) use ($isGestor) {

                    if ($this->record->status === 'Eliminado') {

                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido já foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        return redirect(filament()->getUrl());
                    }

                    DB::transaction(function () use ($data, $isGestor) {
                        $this->validateScheduleWindow();

                        $this->record->update([
                            'status' => $isGestor ? 'Recusado DP' : 'Recusado',
                            'response' => $data['response'],
                            'responded_at' => now(),
                        ]);

                        extract($this->getScheduleDetails());

                        Notification::make()
                            ->title("Pedido de troca recusado")
                            ->body("Recusou o pedido de {$requestername} para a aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->danger()
                            ->send();

                        Notification::make()
                            ->title("Pedido recusado")
                            ->body("O professor {$ownername} recusou o seu pedido de troca da aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->danger()
                            ->actions([
                                NotificationAction::make('Ver Pedido')
                                    ->url(route('filament.admin.resources.schedule-requests.edit', [
                                        'record' => $this->record->getKey(),
                                    ]))
                            ])
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Escalar situação
        if (($isRequestOwner || $isGestor) && $status === 'Recusado') {
            $actions[] = Action::make('escalar')
                ->label('Escalar Situação')
                ->visible(fn() => $this->record->status !== 'Eliminado')
                ->color('warning')
                ->mountUsing(function () {
                    if ($this->record->status === 'Eliminado') {
                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        // Previne a abertura do modal
                        redirect(request()->header('Referer') ?? url()->previous() ?? filament()->getUrl());
                    }
                })
                ->form([
                    Textarea::make('scaled_justification')->label('Justificação')->required(),
                ])
                ->action(function (array $data) {

                    if ($this->record->status === 'Eliminado') {

                        Notification::make()
                            ->title('Pedido já eliminado')
                            ->body('Este pedido já foi eliminado por outro utilizador.')
                            ->danger()
                            ->send();

                        return redirect(filament()->getUrl());
                    }
                    DB::transaction(function () use ($data) {
                        $this->validateScheduleWindow();

                        $this->record->update([
                            'status' => 'Escalado',
                            'scaled_justification' => $data['scaled_justification'],
                            'justification_at' => now(),
                        ]);

                        extract($this->getScheduleDetails());

                        Notification::make()
                            ->title('Pedido Escalado')
                            ->warning()
                            ->body("O pedido foi escalado para análise superior.")
                            ->send();

                        Notification::make()
                            ->title("Pedido de troca escalado")
                            ->body("O professor {$requestername} escalou o pedido de troca da aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->warning()
                            ->actions([
                                NotificationAction::make('Ver Pedido')
                                    ->url(route('filament.admin.resources.schedule-requests.edit', [
                                        'record' => $this->record->getKey(),
                                    ]))
                            ])
                            ->sendToDatabase($owner);

                        Notification::make()
                            ->title("Pedido escalado")
                            ->body("O seu pedido de troca foi escalado para análise superior.")
                            ->warning()
                            ->actions([
                                NotificationAction::make('Ver Pedido')
                                    ->url(route('filament.admin.resources.schedule-requests.edit', [
                                        'record' => $this->record->getKey(),
                                    ]))
                            ])
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        $actions[] = Action::make('cancel')
            ->label('Cancelar')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');

        // $actions[] = DeleteAction::make()
        //     ->label('Eliminar Horário')
        //     ->color('danger')
        //     ->visible(fn() => $this->record->status !== 'Eliminado')
        //     ->requiresConfirmation()
        //     ->action(function () {

        //         if ($this->record->status === 'Eliminado') {

        //             Notification::make()
        //                 ->title('Pedido já eliminado')
        //                 ->body('Este pedido já foi eliminado por outro utilizador.')
        //                 ->danger()
        //                 ->send();

        //             return redirect(filament()->getUrl());
        //         }

        //         try {
        //             DB::transaction(function () {
        //                 $teacherId = Filament::auth()->user()?->teacher?->id;
        //                 $isGestor = in_array(Filament::auth()->id(), [1]);

        //                 $scheduleRequest = $this->record;
        //                 $scheduleNew = $scheduleRequest->scheduleNew;
        //                 $scheduleConflict = $scheduleRequest->scheduleConflict;

        //                 $deletedSchedule = null;
        //                 $scheduleToApprove = null;

        //                 if ($teacherId === $scheduleRequest->id_teacher) {
        //                     $deletedSchedule = $scheduleNew;
        //                     $scheduleToApprove = $scheduleConflict;
        //                 } elseif ($teacherId === $scheduleConflict?->id_teacher) {
        //                     $deletedSchedule = $scheduleConflict;
        //                     $scheduleToApprove = $scheduleNew;
        //                 } else {
        //                     throw new \Exception('Não tem permissão para eliminar esta marcação.');
        //                 }

        //                 if ($deletedSchedule) {
        //                     if (in_array($deletedSchedule->status, ['Aprovado', 'Aprovado DP'])) {
        //                         $this->hoursCounterUpdate($deletedSchedule, true);
        //                     }

        //                     //NODEL
        //                     $deletedSchedule->update(['status' => 'Eliminado']);
        //                 }

        //                 if ($scheduleToApprove && $scheduleToApprove->status !== 'Aprovado') {
        //                     $scheduleToApprove->update([
        //                         'status' => $isGestor ? 'Aprovado DP' : 'Aprovado',
        //                     ]);

        //                     $this->hoursCounterUpdate($scheduleToApprove, false);
        //                 }




        //                 $scheduleRequest->update([
        //                     'status' => $isGestor ? 'Aprovado DP' : 'Aprovado',
        //                     'responded_at' => now(),
        //                 ]);

        //                 Notification::make()
        //                     ->title('Marcação eliminada e troca aprovada')
        //                     ->body('A sua marcação foi eliminada. A marcação da outra parte foi automaticamente aprovada.')
        //                     ->success()
        //                     ->sendToDatabase(Filament::auth()->user());

        //                 $this->redirect($this->getResource()::getUrl('index'));
        //             });
        //         } catch (\Exception $e) {
        //             Notification::make()
        //                 ->title('Erro ao eliminar o horário')
        //                 ->body($e->getMessage())
        //                 ->danger()
        //                 ->send();
        //         }
        //     });


        $actions[] = DeleteAction::make()
            ->label('Eliminar Horário')
            ->color('danger')
            ->visible(fn() => $this->record->status !== 'Eliminado')
            ->requiresConfirmation()
            ->action(function () {
                if ($this->record->status === 'Eliminado') {
                    Notification::make()
                        ->title('Pedido já eliminado')
                        ->body('Este pedido já foi eliminado por outro utilizador.')
                        ->danger()
                        ->send();

                    return redirect(filament()->getUrl());
                }

                try {
                    DB::transaction(function () {
                        $user = Filament::auth()->user();
                        $teacherId = $user?->teacher?->id;
                        $isGestor = in_array($user->id, [1]); // Ajustar conforme necessário

                        $scheduleRequest = $this->record->refresh();
                        $scheduleNew = $scheduleRequest->scheduleNew;
                        $scheduleConflict = $scheduleRequest->scheduleConflict;

                        $deletedSchedule = null;
                        $scheduleToApprove = null;

                        // Determina quem está a eliminar e qual é a marcação dele
                        if ($teacherId === $scheduleRequest->id_teacher) {
                            $deletedSchedule = $scheduleNew;
                            $scheduleToApprove = $scheduleConflict;
                        } elseif ($teacherId === $scheduleConflict?->id_teacher) {
                            $deletedSchedule = $scheduleConflict;
                            $scheduleToApprove = $scheduleNew;
                        } else {
                            throw new \Exception('Não tem permissão para eliminar esta marcação.');
                        }

                        // Elimina a marcação, se ainda não estiver eliminada
                        if ($deletedSchedule && $deletedSchedule->status !== 'Eliminado') {
                            if (in_array($deletedSchedule->status, ['Aprovado', 'Aprovado DP'])) {
                                $this->hoursCounterUpdate($deletedSchedule, true);
                            }

                            $deletedSchedule->update(['status' => 'Eliminado']);

                            // ✅ Atualiza o estado do pedido imediatamente após eliminação
                            $scheduleRequest->status = $isGestor ? 'Aprovado DP' : 'Aprovado';
                            $scheduleRequest->responded_at = now();
                            $scheduleRequest->save();
                        }

                        // Aprova a marcação da outra parte, se ainda não estiver aprovada
                        if ($scheduleToApprove && $scheduleToApprove->status !== 'Aprovado') {
                            $scheduleToApprove->update([
                                'status' => $isGestor ? 'Aprovado DP' : 'Aprovado',
                            ]);

                            $this->hoursCounterUpdate($scheduleToApprove, false);
                        }

                        // Notificação final de sucesso
                        Notification::make()
                            ->title('Marcação eliminada e troca aprovada')
                            ->body('A sua marcação foi eliminada. A marcação da outra parte foi automaticamente aprovada.')
                            ->success()
                            ->sendToDatabase($user);

                        $this->redirect($this->getResource()::getUrl('index'));
                    });
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erro ao eliminar o horário')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });


        return $actions;
    }

    protected function getAvailableRooms(): array
    {
        $conflict = $this->record->scheduleConflict;

        if (!$conflict) return [];

        return Room::where('id_building', $conflict->room?->id_building)
            ->whereDoesntHave('schedules', function ($query) use ($conflict) {
                $query->where('id_timeperiod', $conflict->id_timeperiod)
                    ->where('id_weekday', $conflict->id_weekday);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getScheduleDetails(): array
    {
        $conflict = $this->record->scheduleConflict;

        return [
            'requester' => $this->record->requester?->user,
            'requestername' => $this->record->requester?->name ?? 'desconhecido',
            'owner' => $conflict?->teacher?->user,
            'ownername' => $conflict?->teacher?->name ?? 'desconhecido',
            'currentRoom' => $conflict?->room?->name ?? 'desconhecida',
            'dayName' => $conflict?->weekday?->weekday ?? 'desconhecido',
            'timePeriod' => $conflict?->timeperiod?->description ?? 'desconhecido',
        ];
    }
}

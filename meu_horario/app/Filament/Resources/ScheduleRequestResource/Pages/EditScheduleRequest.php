<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\CheckScheduleWindow;
use App\Models\Room;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;

    use CheckScheduleWindow;

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
        if (($isReceiver || $isGestor) && $status !== 'Recusado') {
            $actions[] = Action::make('accept')
                ->label('Aceitar Troca')
                ->color('success')
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

                        ScheduleResource::hoursCounterUpdate($this->record->scheduleNew, false);

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
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Recusar troca
        if (($isReceiver || $isGestor) && $status !== 'Aprovado') {
            $actions[] = Action::make('reject')
                ->label('Recusar Troca')
                ->color('danger')
                ->form([
                    Textarea::make('response')->label('Justificação para Recusa')->required(),
                ])
                ->action(function (array $data) use ($isGestor) {
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
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Escalar situação
        if (($isRequestOwner || $isGestor) && $status === 'Recusado') {
            $actions[] = Action::make('escalar')
                ->label('Escalar Situação')
                ->color('warning')
                ->form([
                    Textarea::make('justification_escalada')->label('Justificação')->required(),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->validateScheduleWindow();

                        $this->record->update([
                            'status' => 'Escalado',
                            'justification_escalada' => $data['justification_escalada'],
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
                            ->sendToDatabase($owner);

                        Notification::make()
                            ->title("Pedido escalado")
                            ->body("O seu pedido de troca foi escalado para análise superior.")
                            ->warning()
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Cancelar pedido
        if (($isRequestOwner || $isGestor) && $status === 'Pendente') {
            $actions[] = Action::make('cancelRequest')
                ->label('Cancelar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar este pedido?')
                ->action(function () {
                    DB::transaction(function () {
                        $this->validateScheduleWindow();

                        $this->record->update(['status' => 'Cancelado']);
                        $this->record->scheduleNew?->update(['status' => 'Cancelado']);

                        extract($this->getScheduleDetails());

                        Notification::make()
                            ->title("Pedido cancelado")
                            ->body("Cancelou o pedido de troca com {$ownername}, relativo à aula na sala {$currentRoom} ({$dayName}, {$timePeriod}).")
                            ->success()
                            ->send();

                        Notification::make()
                            ->title("Pedido cancelado")
                            ->body("O professor {$requestername} cancelou o pedido de troca da aula na sala {$currentRoom}, no {$dayName} às {$timePeriod}.")
                            ->success()
                            ->sendToDatabase($owner);

                        Notification::make()
                            ->title("Pedido cancelado com sucesso")
                            ->body("Cancelou o pedido de troca com {$ownername}.")
                            ->success()
                            ->sendToDatabase($requester);
                    });

                    return redirect($this->getResource()::getUrl('index'));
                });
        }

        // Eliminar definitivamente
        $actions[] = DeleteAction::make('cancelarPedido')
            ->label('Eliminar Pedido')
            ->color('danger')
            ->visible(fn() => Filament::auth()->user()?->teacher?->id === $this->record->id_teacher)
            ->requiresConfirmation()
            ->modalHeading('Cancelar Pedido de Troca')
            ->modalDescription('Isto irá eliminar o pedido e o horário criado para a troca.')
            ->after(function ($record) {
                try {
                    DB::transaction(function () use ($record) {
                        $schedule = $record->scheduleNew;

                        if ($schedule) {
                            $schedule->delete();
                            ScheduleResource::hoursCounterUpdate($schedule, true);
                        }

                        $owner = $record->scheduleConflict?->teacher?->user;
                        $idMarcacao = $schedule?->id ?? 'N/A';

                        Notification::make()
                            ->title('Pedido eliminado com sucesso')
                            ->body("O pedido de troca foi removido e o horário pendente: {$idMarcacao} foi eliminado.")
                            ->success()
                            ->sendToDatabase($owner);
                    });
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erro ao eliminar o pedido')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->successRedirectUrl($this->getResource()::getUrl('index'));

        $actions[] = Action::make('cancel')
            ->label('Cancelar')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');

        return $actions;
    }

    protected function getAvailableRooms(): array
    {
        $conflict = $this->record->scheduleConflict;

        if (!$conflict) return [];

        return Room::where('id_building', $conflict->room?->building_id)
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

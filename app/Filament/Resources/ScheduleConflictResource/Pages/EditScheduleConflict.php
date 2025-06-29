<?php

namespace App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Filament\Resources\ScheduleConflictResource;
use App\Filament\Resources\ScheduleResource;
use App\Models\Room;

use App\Models\User;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Container\Attributes\Log;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;


class EditScheduleConflict extends EditRecord
{
    protected static string $resource = ScheduleConflictResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $this->record->loadMissing([
            'scheduleConflict.teacher',
            'scheduleConflict.room',
            'scheduleConflict.weekday',
            'scheduleConflict.timePeriod',
            'requester',
        ]);


        return $data;
    }


    protected function getFormActions(): array
    {  // USER CLASSS
        $actions = [];

        if (Auth::user()?->isGestorConflitos()) {
            $actions[] = Action::make('aprovar')
                ->label('Aprovar Pedido')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Select::make('id_room_novo')
                        ->label('Sala Nova')
                        ->required()
                        ->preload()
                        ->options(fn() => $this->getAvailableRooms()),

                    Textarea::make('response_coord')
                        ->label('Justificação para Aprovação DP')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status' => 'Aprovado DP',
                            'response_coord' => $data['response_coord'],
                        ]);

                        $this->record->scheduleConflict?->update([
                            'status' => 'Aprovado DP',
                            'id_room' => $data['id_room_novo'],
                        ]);

                        $this->record->scheduleNew?->update([
                            'status' => 'Aprovado DP',
                        ]);

                        $salaAntiga = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';
                        $salaNova = \App\Models\Room::find($data['id_room_novo'])?->name ?? 'desconhecida';
                        $requester = $this->record->requester?->user;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $owner = $this->record->scheduleConflict?->teacher?->user;
                        $ownername = $owner?->name ?? 'desconhecido';

                        Notification::make()
                            ->title('Troca Aprovada')
                            ->success()
                            ->body("O pedido foi aprovado. {$requestername} sala: {$salaAntiga} | {$ownername} sala: {$salaNova}.")
                            ->send();

                        Notification::make()
                            ->title('Pedido de troca aprovado por Direção Pedagógica')
                            ->success()
                            ->body("O seu pedido de troca da sala {$salaAntiga} para {$salaNova} foi aprovado.")
                            ->sendToDatabase($requester);

                        Notification::make()
                            ->title('Pedido de troca aprovado por Direção Pedagógica')
                            ->success()
                            ->body("Aprovou o pedido de {$requestername} na troca da sala {$salaAntiga} para {$salaNova}.")
                            ->sendToDatabase($owner);

                        \App\Filament\Resources\ScheduleResource::hoursCounterUpdate($this->record->scheduleNew, false);
                    });
                });

            $actions[] = Action::make('recusar')
                ->label('Recusar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('response_coord')
                        ->label('Justificação para Recusa DP')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status' => 'Recusado DP',
                            'response_coord' => $data['response_coord'],
                        ]);

                        $this->record->scheduleNew?->update([
                            'status' => 'Recusado DP',
                        ]);

                        $this->record->scheduleConflict?->update([
                            'status' => 'Aprovado',
                        ]);

                        $schedule = $this->record->scheduleConflict;
                        $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                        $salaNova = $this->record->scheduleNew?->room?->name ?? 'não definida';
                        $requester = $this->record->requester?->user;
                        $requestername = $this->record->requester?->name ?? 'desconhecido';
                        $owner = $this->record->scheduleConflict?->teacher?->user;
                        $ownername = $owner?->name ?? 'desconhecido';

                        Notification::make()
                            ->title('Troca Recusada')
                            ->danger()
                            ->body("O pedido foi recusado. {$requestername} sala: {$salaAntiga} | {$ownername} sala: {$salaNova}.")
                            ->send();

                        Notification::make()
                            ->title('Pedido de troca recusado por Direção Pedagógica')
                            ->danger()
                            ->body("O seu pedido de troca da sala {$salaAntiga} para {$salaNova} foi recusado.")
                            ->sendToDatabase($requester);

                        Notification::make()
                            ->title('Pedido de troca recusado por Direção Pedagógica')
                            ->danger()
                            ->body("Recusou o pedido de {$requestername} na troca da sala {$salaAntiga} para {$salaNova}.")
                            ->sendToDatabase($owner);
                    });
                });
        }


        $actions[] = DeleteAction::make()
            ->label('Eliminar Horário123')
            ->color('danger')
            ->requiresConfirmation()
            // ->visible(function () {
            //     $user = Filament::auth()->user();
            //     return $user?->teacher?->id === $this->record->id_teacher;
            // })
            ->action(function () {


                try {
                    DB::transaction(function () {
                        // Verifica se o registro tem um scheduleConflict e scheduleNew

                        if ($this->record->status === 'Aprovado DP') {
                            ScheduleResource::hoursCounterUpdate($this->record->scheduleNew, true);


                            $this->record->delete();
                            $this->record->scheduleNew?->delete();
                            $this->record->scheduleConflict?->update([
                                'status' => 'Aprovado',
                            ]);
                        }


                        if ($this->record->status === 'Escalado') {
                            ScheduleResource::hoursCounterUpdate($this->record->scheduleConflict, true);


                            $this->record->delete();
                            $this->record->scheduleConflict?->delete();
                            $this->record->scheduleNew?->update([
                                'status' => 'Aprovado',
                            ]);
                            ScheduleResource::hoursCounterUpdate($this->record->scheduleNew, false);
                        }




                        Notification::make()
                            ->title("Horário Eliminado")
                            ->body("O horário foi eliminado com sucesso, 1:{$this->record->id}. 2:{$this->record->scheduleNew?->id}, 3:{$this->record->scheduleConflict?->id}")
                            ->success()
                            ->sendToDatabase(Filament::auth()->user());

                        Notification::make()
                            ->title('Horário Eliminado')
                            ->body("O horário foi eliminado com sucesso ID:{$this->record->scheduleNew?->id}")
                            ->success()
                            ->send();
                        $this->redirect(filament()->getUrl());
                    });
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erro ao eliminar o horário')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        $actions[] = $this->getCancelFormAction();
        return $actions;
    }

    protected function getAvailableRooms(): array
    {
        $this->record->loadMissing('scheduleConflict.room');

        $conflict = $this->record->scheduleConflict;

        $edificioId = $conflict->room?->id_building;
        $idTimePeriod = $conflict->id_timeperiod;
        $idWeekday = $conflict->id_weekday;

        if (is_null($edificioId) || is_null($idTimePeriod) || is_null($idWeekday)) {
            return [];
        }

        return Room::where('id_building', $edificioId)
            ->whereDoesntHave('schedules', function ($query) use ($idTimePeriod, $idWeekday) {
                $query->where('id_timeperiod', $idTimePeriod)
                    ->where('id_weekday', $idWeekday);
            })
            ->get()
            ->unique('name')
            ->pluck('name', 'id')
            ->toArray();
    }


    // protected function getHeaderActions(): array
    // {
    //     return [


    //     ];
    // }
}

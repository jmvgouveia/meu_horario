<?php

namespace App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Filament\Resources\ScheduleConflictResource;
use App\Filament\Resources\ScheduleResource;
use App\Models\Room;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
        // dd($this->record->toArray());

        return $data;
    }

    protected function getFormActions(): array
    {
        return [

            Action::make('aprovar')
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
                    $this->record->update([
                        'status' => 'Aprovado DP',
                        'response_coord' => $data['response_coord'],
                    ]);

                    $this->record->scheduleConflict?->update([
                        'status' => 'Aprovado DP',
                        'id_room' => $data['id_room_novo'],
                    ]);

                    $this->record->scheduleNovo?->update([
                        'status' => 'Aprovado DP',
                    ]);




                    $salaAntiga = $this->record->scheduleConflict?->room?->name ?? 'desconhecida';
                    $salaNova = Room::find($data['id_room_novo'])?->name ?? 'desconhecida';
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
                        ->sendToDatabase($requester); // Envia e armazena no banco de dados

                    Notification::make()
                        ->title('Pedido de troca aprovado por Direção Pedagógica')
                        ->success()
                        ->body("Aprovou o pedido de {$requestername} na troca da sala {$salaAntiga} para {$salaNova}.")
                        ->sendToDatabase($owner); // Envia e armazena no banco de dados

                    // Atualiza numero de horas
                    // SchedulesResource::hoursCounterUpdate($this->record->scheduleNovo, true);
                    ScheduleResource::hoursCounterUpdate($this->record->scheduleNovo, false);
                }),

            Action::make('recusar')
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
                    $this->record->update([
                        'status' => 'Recusado DP',
                        'response_coord' => $data['response_coord'],
                    ]);

                    // Atualiza o status do scheduleConflict para "Recusado DP"
                    // e mantém a sala original
                    $this->record->scheduleNovo?->update([
                        'status' => 'Recusado DP',
                    ]);


                    $this->record->scheduleConflict?->update([
                        'status' => 'Aprovado',
                        // 'id_room' => $data['id_room_novo'],
                    ]);

                    $schedule = $this->record->scheduleConflict;
                    $salaAntiga = $schedule?->room?->name ?? 'desconhecida';
                    $salaNova = $this->record->scheduleNovo?->room?->name ?? 'não definida';
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
                        ->sendToDatabase($requester); // Envia e armazena no banco de dados

                    Notification::make()
                        ->title('Pedido de troca recusado por Direção Pedagógica')
                        ->danger()
                        ->body("Recusou o pedido de {$requestername} na troca da sala {$salaAntiga} para {$salaNova}.")
                        ->sendToDatabase($owner); // Envia e armazena no banco de dados


                }),

            $this->getCancelFormAction(), // Botão "Cancelar"
        ];
    }


    protected function getAvailableRooms(): array
    {
        $this->record->loadMissing('scheduleConflict.room');

        $conflict = $this->record->scheduleConflict;

        $edificioId = $conflict->room?->building_id;
        $idTimePeriod = $conflict->id_timeperiod;
        $idWeekday = $conflict->id_weekday;

        if (is_null($edificioId) || is_null($idTimePeriod) || is_null($idWeekday)) {
            return [];
        }

        return Room::where('building_id', $edificioId)
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
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}

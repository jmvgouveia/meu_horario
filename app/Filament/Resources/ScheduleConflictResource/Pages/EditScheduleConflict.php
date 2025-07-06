<?php

namespace App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Filament\Resources\ScheduleConflictResource;
use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Traits\HourCounter;
use App\Helpers\UserHelper;
use App\Models\Room;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DatabaseHelper as DBHelper;
use App\Helpers\MensagensErro as MSGErro;
use App\Models\Schedule;
use App\Models\ScheduleRequest;

class EditScheduleConflict extends EditRecord
{
    use HourCounter;
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
    {
        $actions = [];

        if (UserHelper::currentUser()?->isGestorConflitos()) {
            $actions[] = Action::make('aprovar')
                ->label('Aprovar Pedido')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => !in_array($this->record->status, ['Eliminado', 'Aprovado DP']))

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

                        DBHelper::updateScheduleRequestData(
                            $this->record->id,
                            [
                                'status' => 'Aprovado DP',

                                'response_coord' => $data['response_coord'],
                            ],
                            MSGErro::ERRO_APROVAR_SCHEDULE
                        );
                        // $this->record->update([
                        //     'status' => 'Aprovado DP',
                        //     'response_coord' => $data['response_coord'],
                        // ]);

                        DBHelper::updateScheduleData(
                            $this->record->scheduleConflict->id,
                            [
                                'status' => 'Aprovado DP',
                                'id_room' => $data['id_room_novo'],
                            ],
                            MSGErro::ERRO_APROVAR_SCHEDULE
                        );
                        // $this->record->scheduleConflict?->update([
                        //     'status' => 'Aprovado DP',
                        //     'id_room' => $data['id_room_novo'],
                        // ]);

                        DBHelper::updateScheduleData(
                            $this->record->scheduleNew->id,
                            [
                                'status' => 'Aprovado DP',
                            ],
                            MSGErro::ERRO_APROVAR_SCHEDULE
                        );

                        // $this->record->scheduleNew?->update([
                        //     'status' => 'Aprovado DP',
                        // ]);



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
                            ->sendToDatabase($requester);

                        Notification::make()
                            ->title('Pedido de troca aprovado por Direção Pedagógica')
                            ->success()
                            ->body("Aprovou o pedido de {$requestername} na troca da sala {$salaAntiga} para {$salaNova}.")
                            ->sendToDatabase($owner);

                        //ScheduleResource::hoursCounterUpdate($this->record->scheduleNew, false);
                        $this->hoursCounterUpdate($this->record->scheduleNew, false);
                    });
                });

            $actions[] = Action::make('recusar')
                ->label('Recusar Pedido')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn() => !in_array($this->record->status, ['Eliminado', 'Aprovado DP']))

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
                    Textarea::make('response_coord')
                        ->label('Justificação para Recusa DP')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {

                        DBHelper::updateScheduleRequestData(
                            $this->record->id,
                            [
                                'status' => 'Recusado DP',
                                'response_coord' => $data['response_coord'],
                            ],
                            MSGErro::ERRO_ATUALIZAR_SCHEDULE
                        );

                        // $this->record->update([
                        //     'status' => 'Recusado DP',
                        //     'response_coord' => $data['response_coord'],
                        // ]);

                        DBHelper::updateScheduleData(
                            $this->record->scheduleNew?->id,
                            [
                                'status' => 'Recusado DP',
                            ],
                            MSGErro::ERRO_ATUALIZAR_SCHEDULE
                        );

                        // $this->record->scheduleNew?->update([
                        //     'status' => 'Recusado DP',
                        // ]);


                        DBHelper::updateScheduleData(
                            $this->record->scheduleConflict->id,
                            ['status' => 'Aprovado'],
                            MSGErro::ERRO_ATUALIZAR_SCHEDULE
                        );


                        // $this->record->scheduleConflict?->update([
                        //     'status' => 'Aprovado',
                        // ]);

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
        $actions[] = $this->getCancelFormAction();

        $actions[] = DeleteAction::make()
            ->label('Eliminar Horário')
            ->color('danger')
            ->visible(function () {
                $user = Filament::auth()->user();
                $teacherId = $user?->teacher?->id;

                if (!$teacherId || $this->record->status === 'Eliminado') {
                    return false;
                }

                $idTeacherRequester = $this->record->id_teacher;
                $idTeacherOwner = Schedule::where('id', $this->record->id_schedule)->value('id_teacher');

                if ($user->hasRole('Gestor Conflitos')) {
                    return $teacherId === $idTeacherRequester;
                }

                return $teacherId === $idTeacherRequester || $teacherId === $idTeacherOwner;
            })
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
            ->requiresConfirmation()
            ->action(function () {
                try {
                    DB::transaction(function () {

                        switch ($this->record->status) {
                            case 'Aprovado DP':

                                DBHelper::updateScheduleRequestData(
                                    $this->record->id,
                                    ['status' => 'Eliminado'],
                                    'Erro ao eliminar pedido Aprovado DP'
                                );
                                // $this->record->update(['status' => 'Eliminado']);

                                DBHelper::updateScheduleData(
                                    $this->record->scheduleNew->id,
                                    ['status' => 'Eliminado'],
                                    'Erro ao eliminar horário novo'
                                );

                                //$this->record->scheduleNew?->update(['status' => 'Eliminado']);

                                DBHelper::updateScheduleData(
                                    $this->record->scheduleConflict->id,
                                    ['status' => 'Aprovado'],
                                    'Erro ao reaprovar horário original'
                                );
                                //$this->record->scheduleConflict?->update(['status' => 'Aprovado']);


                                break;

                            case 'Escalado':

                                DBHelper::updateScheduleRequestData(
                                    $this->record->id,
                                    ['status' => 'Eliminado'],
                                    MSGErro::ERRO_ELIMINAR_SCHEDULE
                                );
                                // $this->record->update(['status' => 'Eliminado']);


                                if (UserHelper::currentUser()?->id === $this->record->scheduleConflict?->teacher?->user?->id) {

                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleConflict->id,
                                        ['status' => 'Eliminado'],
                                        MSGErro::ERRO_ELIMINAR_SCHEDULE
                                    );

                                    //$this->record->scheduleConflict?->update(['status' => 'Eliminado',]);

                                    $this->hoursCounterUpdate($this->record->scheduleConflict, true);

                                    $this->hoursCounterUpdate($this->record->scheduleNew, false);
                                } else {


                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleNew->id,
                                        ['status' => 'Eliminado'],
                                        MSGErro::ERRO_ELIMINAR_SCHEDULE
                                    );


                                    //$this->record->scheduleNew?->update(['status' => 'Eliminado']);
                                }


                                if (UserHelper::currentUser()?->id === $this->record->scheduleNew?->teacher?->user?->id) {

                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleConflict->id,
                                        ['status' => 'Aprovado'],
                                        MSGErro::ERRO_APROVAR_SCHEDULE
                                    );

                                    //   $this->record->scheduleConflict?->update(['status' => 'Aprovado']);

                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleNew?->id,
                                        ['status' => 'Eliminado'],
                                        MSGErro::ERRO_ELIMINAR_SCHEDULE
                                    );

                                    //  $this->record->scheduleNew?->update(['status' => 'Eliminado']);
                                } else {


                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleConflict->id,
                                        ['status' => 'Eliminado'],
                                        MSGErro::ERRO_ELIMINAR_SCHEDULE
                                    );
                                    DBHelper::updateScheduleData(
                                        $this->record->scheduleNew->id,
                                        ['status' => 'Aprovado'],
                                        MSGErro::ERRO_APROVAR_SCHEDULE
                                    );


                                    // $this->record->scheduleConflict?->update(['status' => 'Eliminado']);

                                    // $this->record->scheduleNew?->update(['status' => 'Aprovado']);
                                }
                                break;

                            default:
                                Notification::make()
                                    ->title('Estado não tratado')
                                    ->body('O estado do horário não foi tratado para eliminação.')
                                    ->danger()
                                    ->send();
                                throw new \Exception('Estado não tratado');
                        }


                        Notification::make()
                            ->title("Horário Eliminado")
                            ->body("O horário foi eliminado com sucesso, e o conflito foi resolvido.")
                            ->success()
                            ->sendToDatabase(Filament::auth()->user());

                        Notification::make()
                            ->title('Horário Eliminado')
                            ->body("O horário foi eliminado com sucesso.")
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
                    ->where('id_weekday', $idWeekday)
                    ->where('status', '!=', 'Eliminado');
            })
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }
    protected function getRedirectUrl(): string
    {
        return filament()->getUrl();
    }
}

<?php

namespace App\Filament\Resources\ScheduleResource\Traits;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\DB;

trait HandlesScheduleSwap
{
    // public function submitJustification(array $data)
    // {

    //     try {
    //         DB::transaction(function () use ($data) {

    //             $formState = $this->form->getState();

    //             $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
    //             $activeYear = SchoolYear::where('active', true)->first();

    //             $schedule = Schedule::create([
    //                 'id_room' => $this->conflictingSchedule->id_room,
    //                 'id_weekday' => $this->conflictingSchedule->id_weekday,
    //                 'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
    //                 'id_teacher' => $teacher?->id,
    //                 'id_subject' => $formState['id_subject'] ?? null,
    //                 'shift' => $formState['shift'] ?? null,
    //                 'id_schoolyear' => $activeYear?->id,
    //                 'status' => 'Pendente',
    //             ]);

    //             $schedule->classes()->sync($formState['id_classes'] ?? []);
    //             $schedule->students()->sync($formState['students'] ?? []);

    //             $scheduleRequest = ScheduleRequest::create([
    //                 'id_schedule' => $this->conflictingSchedule->id,
    //                 'id_teacher' => $teacher?->id,
    //                 'id_new_schedule' => $schedule->id,
    //                 'justification' => $data['justification'] ?? 'Conflito detetado automaticamente.',
    //                 'status' => 'Pendente',
    //             ]);

    //             $scheduleRequest->loadMissing('requester.user', 'scheduleConflict.teacher.user');
    //             $schedule->loadMissing('weekday', 'timeperiod', 'room');

    //             $requester = $scheduleRequest->requester?->user;
    //             $owner = $scheduleRequest->scheduleConflict?->teacher?->user;
    //             $currentRoom = $schedule?->room?->name ?? 'desconhecida';
    //             $dayName = $schedule?->weekday?->weekday ?? 'desconhecido';
    //             $timePeriod = $schedule->timeperiod?->description ?? 'desconhecido';
    //             $requestername = $requester?->name ?? 'um professor';

    //             Notification::make()
    //                 ->title("Pedido de Troca criado com sucesso!")
    //                 ->body("O seu pedido de troca da sala {$currentRoom}, na {$dayName}, entre {$timePeriod}, foi enviado com sucesso para {$owner?->name}.")
    //                 ->persistent()
    //                 ->success()
    //                 ->send();


    //             Notification::make()
    //                 ->title("Novo pedido de troca recebido")
    //                 ->body("O(a) professor(a) {$requestername} solicitou trocar a sala {$currentRoom}, marcada para {$dayName} entre {$timePeriod}.")
    //                 ->success()
    //                 ->actions([
    //                     Action::make('Ver Pedido')
    //                         ->url(route('filament.admin.resources.schedule-requests.edit', [
    //                             'record' => $scheduleRequest->id,
    //                         ])),
    //                 ])
    //                 ->sendToDatabase($owner);
    //         });

    //         return redirect($this->getResource()::getUrl('index'));
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Erro ao submeter o pedido de troca')
    //             ->body($e->getMessage())
    //             ->danger()
    //             ->send();
    //         throw $e;
    //     }
    // }


    public function submitJustification(array $data)
    {
        try {
            DB::transaction(function () use ($data) {
                $formState = $this->form->getState();
                $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
                $activeYear = SchoolYear::where('active', true)->first();

                // ✅ 1. Obter o último horário ocupado no mesmo dia/período
                $ultimoHorario = Schedule::where('id_weekday', $this->conflictingSchedule->id_weekday)
                    ->where('id_timeperiod', $this->conflictingSchedule->id_timeperiod)
                    ->whereIn('status', ['Aprovado', 'Pendente'])
                    ->latest('updated_at')
                    ->first();

                if (!$ultimoHorario) {
                    throw new \Exception('Não foi encontrado horário válido para este slot.');
                }

                // ✅ 2. Criar o novo Schedule pendente
                $schedule = Schedule::create([
                    'id_room' => $this->conflictingSchedule->id_room,
                    'id_weekday' => $this->conflictingSchedule->id_weekday,
                    'id_timeperiod' => $this->conflictingSchedule->id_timeperiod,
                    'id_teacher' => $teacher?->id,
                    'id_subject' => $formState['id_subject'] ?? null,
                    'shift' => $formState['shift'] ?? null,
                    'id_schoolyear' => $activeYear?->id,
                    'status' => 'Pendente',
                ]);

                $schedule->classes()->sync($formState['id_classes'] ?? []);
                $schedule->students()->sync($formState['students'] ?? []);

                // ✅ 3. Verificar se já há pedido pendente para esse horário
                $hasPending = ScheduleRequest::where('id_schedule', $ultimoHorario->id)
                    ->where('status', 'Pendente')
                    ->exists();

                $status = $hasPending ? 'Aguardando' : 'Pendente';

                // ✅ 4. Criar o pedido de troca encadeado
                $scheduleRequest = ScheduleRequest::create([
                    'id_schedule' => $ultimoHorario->id, // ← encadeado corretamente
                    'id_teacher' => $teacher?->id,
                    'id_new_schedule' => $schedule->id,
                    'justification' => $data['justification'] ?? 'Conflito detetado automaticamente.',
                    'status' => $status,
                ]);

                // Notificação
                $scheduleRequest->loadMissing('requester.user', 'scheduleConflict.teacher.user');
                $schedule->loadMissing('weekday', 'timeperiod', 'room');

                $requester = $scheduleRequest->requester?->user;
                $owner = $scheduleRequest->scheduleConflict?->teacher?->user;
                $currentRoom = $schedule?->room?->name ?? 'desconhecida';
                $dayName = $schedule?->weekday?->weekday ?? 'desconhecido';
                $timePeriod = $schedule->timeperiod?->description ?? 'desconhecido';
                $requesterName = $requester?->name ?? 'um professor';

                Notification::make()
                    ->title("Pedido de Troca criado com sucesso!")
                    ->body("O seu pedido de troca da sala {$currentRoom}, na {$dayName}, entre {$timePeriod}, foi enviado com sucesso para {$owner?->name}.")
                    ->persistent()
                    ->success()
                    ->send();

                Notification::make()
                    ->title("Novo pedido de troca recebido")
                    ->body("O(a) professor(a) {$requesterName} solicitou trocar a sala {$currentRoom}, marcada para {$dayName} entre {$timePeriod}.")
                    ->success()
                    ->actions([
                        Action::make('Ver Pedido')
                            ->url(route('filament.admin.resources.schedule-requests.edit', [
                                'record' => $scheduleRequest->id,
                            ])),
                    ])
                    ->sendToDatabase($owner);
            });

            return redirect($this->getResource()::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao submeter o pedido de troca')
                ->body($e->getMessage())
                ->danger()
                ->send();
            throw $e;
        }
    }
}

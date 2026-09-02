<?php

namespace App\Filament\Resources\ScheduleResource\Traits;

use App\Models\Classes;
use App\Models\Room;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Collection;

trait ValidatesClassBuildings
{
    protected function validateSelectedClassesBelongToRoomBuilding(array $classIds, ?int $roomId): void
    {
        $classIds = Collection::make($classIds)->filter()->values();

        if ($classIds->isEmpty()) {
            return;
        }

        $room = Room::with('building')->find($roomId);

        if (! $room || ! $room->building) {
            Notification::make()
                ->title('Sala inválida')
                ->body('A sala selecionada não tem um edifício associado.')
                ->danger()
                ->persistent()
                ->send();

            throw new Halt('A sala selecionada não tem um edifício associado.');
        }

        $classes = Classes::with('buildings')->whereIn('id', $classIds)->get();

        foreach ($classes as $class) {
            if ($class->buildings->isEmpty()) {
                Notification::make()
                    ->title('Turma sem edifícios permitidos')
                    ->body("A turma {$class->name} não tem edifícios permitidos configurados.")
                    ->danger()
                    ->persistent()
                    ->send();

                throw new Halt("A turma {$class->name} não tem edifícios permitidos configurados.");
            }

            if (! $class->buildings->contains('id', $room->id_building)) {
                Notification::make()
                    ->title('Edifício não permitido')
                    ->body("A turma {$class->name} não permite o edifício {$room->building->name}.")
                    ->danger()
                    ->persistent()
                    ->send();

                throw new Halt("A turma {$class->name} não permite o edifício {$room->building->name}.");
            }
        }
    }
}

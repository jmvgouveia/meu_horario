<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $teacher = $this->record->load('user');

        $data['user']['email'] = $teacher->user->email ?? '';
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Atualizar campos do utilizador, se existir
        if ($record->user) {
            // Atualizar o nome do utilizador com o nome do professor
            $record->user->name = $data['name']; // Atualiza o nome do utilizador com o nome do professor

            // Atualizar o email do utilizador
            $record->user->email = $data['user']['email'];

            // Atualizar a senha, se fornecida
            if (!empty($data['user']['password'])) {
                $record->user->password = Hash::make($data['user']['password']);
            }

            $record->user->saveOrFail();
        }

        unset($data['user']);

        $record->updateOrFail($data);

        //Atualizar o contador de horas com base nas posições e reduções
        $record->load(['positions', 'timeReductions']);
        $record->updateHourCounterFromReductions();

        return $record;
    }
}

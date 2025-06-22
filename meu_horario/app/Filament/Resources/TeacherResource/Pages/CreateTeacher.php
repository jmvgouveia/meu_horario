<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\TeacherHourCounter;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = $data['user'];

        // Validação
        $validator = Validator::make([
            'name' => $data['name'],
            'email' => $userData['email'],
            'password' => $userData['password'],
        ], [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                Notification::make()
                    ->title('Erro ao criar professor')
                    ->body($message)
                    ->danger()
                    ->persistent()
                    ->send();
            }

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // Criar o User
        $user = User::create([
            'name' => $data['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Associar id_user ao professor
        $data['id_user'] = $user->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        TeacherHourCounter::create([
            'id_teacher' => $record->id, // Agora já existe!
            'workload' => 26,
            'teaching_load' => 22,
            'non_teaching_load' => 4,
        ]);
    }

/*     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    } */
}

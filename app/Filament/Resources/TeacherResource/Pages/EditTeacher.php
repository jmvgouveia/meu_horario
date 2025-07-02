<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record->user) {
            $record->user->name = $data['name'];

            $record->user->email = $data['user']['email'];

            if (!empty($data['user']['password'])) {
                $record->user->password = Hash::make($data['user']['password']);
            }

            $record->user->saveOrFail();
        }

        unset($data['user']);

        $record->updateOrFail($data);

        $record->load(['positions', 'timeReductions']);
        $record->updateHourCounterFromReductions();

        return $record;
    }
}

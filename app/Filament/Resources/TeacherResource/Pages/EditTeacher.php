<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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


        // ✅ Atualizar o ano letivo nas tabelas pivot
        $schoolYearId = \App\Models\SchoolYear::where('active', true)->value('id');

        foreach ($record->positions as $position) {
            DB::table('teacher_positions') // ou o nome real da pivot
                ->where('id_teacher', $record->id)
                ->where('id_position', $position->id)
                ->update(['id_schoolyears' => $schoolYearId]);
        }

        foreach ($record->timeReductions as $reduction) {
            DB::table('teacher_time_reductions') // ou o nome real da pivot
                ->where('id_teacher', $record->id)
                ->where('id_time_reduction', $reduction->id)
                ->update(['id_schoolyears' => $schoolYearId]);
        }
        $record->updateHourCounterFromReductions();

        return $record;
    }
}

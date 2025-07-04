<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\Position;
use App\Models\SchoolYear;
use App\Models\TimeReduction;
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

        $schoolYearId = SchoolYear::where('active', true)->value('id');

        foreach ($record->positions as $position) {
            DB::table('teacher_positions')
                ->where('id_teacher', $record->id)
                ->where('id_position', $position->id)
                ->update(['id_schoolyear' => $schoolYearId]);
        }

        foreach ($record->timeReductions as $reduction) {
            DB::table('teacher_time_reductions')
                ->where('id_teacher', $record->id)
                ->where('id_time_reduction', $reduction->id)
                ->update(['id_schoolyear' => $schoolYearId]);
        }

        $record->loadMissing(['positions', 'timeReductions']); // garante que relações estão atualizadas
        $record->updateHourCounterFromReductions($schoolYearId);


        return $record;
    }
}

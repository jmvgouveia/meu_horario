<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\SchoolYear;
use App\Models\TeacherHourCounter;
use App\Models\User;
use App\Services\UserActivationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = $data['user'];

        $validator = Validator::make([
            'name' => $data['name'],
            'email' => $userData['email'],
        ], [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'email', 'unique:users,email'],
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

        $user = User::create([
            'name' => $data['name'],
            'email' => $userData['email'],
            'password' => str()->random(40),
            'is_active' => false,
        ]);

        $user->assignRole('Professor');
        app(UserActivationService::class)->issueAndNotify($user);

        $data['id_user'] = $user->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $activeSchoolYear = SchoolYear::where('active', true)->first();

        TeacherHourCounter::create([
            'id_teacher' => $record->id,
            'workload' => 26,
            'teaching_load' => 22,
            'non_teaching_load' => 4,
            'id_schoolyear' => $activeSchoolYear->id ?? null,
        ]);

        $this->syncPivotWithSchoolYear($this->record);
    }
    protected function syncPivotWithSchoolYear($teacher): void
    {
        $schoolYearId = \App\Models\SchoolYear::where('active', true)->value('id');

        foreach ($teacher->positions as $position) {
            DB::table('teacher_positions')
                ->where('id_teacher', $teacher->id)
                ->where('id_position', $position->id)
                ->update(['id_schoolyear' => $schoolYearId]);
        }

        foreach ($teacher->timeReductions as $reduction) {
            DB::table('teacher_time_reductions')
                ->where('id_teacher', $teacher->id)
                ->where('id_time_reduction', $reduction->id)
                ->update(['id_schoolyear' => $schoolYearId]);
        }
    }
}

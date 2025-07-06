<?php

namespace App\Helpers;

use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class DatabaseHelper
{

    public static function updateScheduleRequestStatus(int $id, bool $isNovo, string $status, string $msgErro)
    {
        try {
            ScheduleRequest::where($isNovo ? 'id_new_schedule' : 'id_schedule', $id)->update(['status' => $status]);
        } catch (\Exception $e) {
            Notification::make()
                ->title($msgErro)
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function updateScheduleStatus(int $id, string $status, string $msgErro)
    {
        try {
            Schedule::where('id', $id)->update(['status' => $status]);
        } catch (\Exception $e) {
            Notification::make()
                ->title($msgErro)
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function updateScheduleRequestData(int $requestId, array $data, string $msgErro)
    {
        if (!$requestId) {
            Notification::make()
                ->title($msgErro)
                ->body('ID do pedido de troca ausente ou inválido.')
                ->danger()
                ->send();
            return;
        }
        try {
            ScheduleRequest::where('id', $requestId)->update($data);
        } catch (\Exception $e) {
            Notification::make()
                ->title($msgErro)
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }


    public static function updateScheduleData(int $scheduleId, array $data, string $msgErro)
    {
        if (!$scheduleId) {
            Notification::make()
                ->title($msgErro)
                ->body('ID do horário ausente ou inválido.')
                ->danger()
                ->send();
            return;
        }
        try {
            Schedule::where('id', $scheduleId)->update($data);
        } catch (\Exception $e) {
            Notification::make()
                ->title($msgErro)
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // GETs
    public static function getScheduleRequestByStatus(int $id, string $status)
    {
        return ScheduleRequest::where('id_schedule', $id)
            ->where('status', $status)
            ->first();
    }

    public static function getIDActiveSchoolyear()
    {
        return SchoolYear::where('active', true)->value('id');
    }

    public static function getCurrentTeacher(): ?Teacher
    {
        return Teacher::where('id_user', Filament::auth()->id())->first();
    }

    public static function getScheduleRequestID(int $id, bool $isNovo)
    {

        return ScheduleRequest::where($isNovo ? 'id_new_schedule' : 'id_schedule', $id)->first();
    }
}

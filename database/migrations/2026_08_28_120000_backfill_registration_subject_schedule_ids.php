<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('registrations_subjects')
            ->whereNull('id_schedule')
            ->whereNotNull('shift')
            ->orderBy('id')
            ->eachById(function (object $registrationSubject): void {
                $legacyScheduleId = (string) $registrationSubject->shift;

                if (! ctype_digit($legacyScheduleId)) {
                    return;
                }

                $schedule = DB::table('schedules')->find((int) $legacyScheduleId);

                if (! $schedule) {
                    return;
                }

                DB::table('registrations_subjects')
                    ->where('id', $registrationSubject->id)
                    ->update([
                        'id_schedule' => $schedule->id,
                        'shift' => $schedule->shift,
                    ]);
            });
    }

    public function down(): void
    {
        // Restoring the ambiguous legacy value would discard the shift name.
    }
};

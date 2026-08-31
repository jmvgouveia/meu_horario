<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activeSchoolYearId = DB::table('schoolyears')
            ->where('active', true)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->value('id');

        if ($activeSchoolYearId) {
            DB::table('schoolyears')
                ->where('active', true)
                ->where('id', '!=', $activeSchoolYearId)
                ->update(['active' => false]);
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX schoolyears_single_active ON schoolyears (active) WHERE active = 1');

            return;
        }

        DB::statement('ALTER TABLE schoolyears ADD COLUMN active_unique TINYINT GENERATED ALWAYS AS (IF(active = 1, 1, NULL)) PERSISTENT');
        DB::statement('CREATE UNIQUE INDEX schoolyears_single_active ON schoolyears (active_unique)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX schoolyears_single_active');

            return;
        }

        DB::statement('DROP INDEX schoolyears_single_active ON schoolyears');
        DB::statement('ALTER TABLE schoolyears DROP COLUMN active_unique');
    }
};

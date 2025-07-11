<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimePeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks if needed
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table
        DB::table('timeperiods')->truncate();

        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Now insert your data
        $periods = [];
        $start = Carbon::createFromTimeString('08:00');
        $endOfDay = Carbon::createFromTimeString('21:00');

        while ($start->lessThanOrEqualTo($endOfDay)) {
            $end = (clone $start)->addHour();

            $periods[] = [
                'description' => $start->format('H:i') . '-' . $end->format('H:i'),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $start->addMinutes(30);
        }

        DB::table('timeperiods')->insert($periods);
    }
}

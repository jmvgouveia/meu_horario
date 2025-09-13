<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_hour_counters', function (Blueprint $table) {
            $table->integer('numovertime')->default(0)->after('authorized_overtime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_hour_counters', function (Blueprint $table) {
            $table->dropColumn('numovertime');

            //
        });
    }
};

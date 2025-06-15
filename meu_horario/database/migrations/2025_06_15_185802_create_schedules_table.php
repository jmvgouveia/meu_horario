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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_schoolyear')
                ->constrained('schoolyears')
                ->cascadeOnDelete();
            $table->foreignId('id_timeperiod')
                ->constrained('timeperiods')
                ->cascadeOnDelete();
            $table->foreignId('id_room')
                ->constrained('rooms')
                ->cascadeOnDelete();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->foreignId('id_weekday')
                ->constrained('weekdays')
                ->cascadeOnDelete();
            $table->foreignId('id_subject')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->string('shift'); // turno
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

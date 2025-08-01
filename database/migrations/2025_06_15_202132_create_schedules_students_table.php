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
        Schema::create('schedules_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_student')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('id_schedule')
                ->constrained('schedules')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules_students');
    }
};

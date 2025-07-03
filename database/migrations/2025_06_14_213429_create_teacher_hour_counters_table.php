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
        Schema::create('teacher_hour_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->integer('workload');
            $table->integer('teaching_load'); // Carga componente letiva
            $table->integer('non_teaching_load'); // Carga componente não-letiva
            $table->boolean('authorized_overtime')->default(false); // Autorizado a fazer horas extra
            $table->foreignId('id_schoolyears')
                ->constrained('schoolyears')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_hour_counters');
    }
};

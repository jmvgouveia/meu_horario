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
        Schema::create('teacher_time_reductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->foreignId('id_time_reduction')
                ->constrained('time_reductions')
                ->cascadeOnDelete();
            $table->foreignId('id_schoolyear')
                ->constrained('schoolyears')
                ->cascadeOnDelete()
                ->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_time_reductions');
    }
};

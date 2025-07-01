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
        Schema::create('last_year_students', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->foreignId('id_subject')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->foreignId('id_student')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('id_schoolyear')
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
        Schema::dropIfExists('last_year_students');
    }
};

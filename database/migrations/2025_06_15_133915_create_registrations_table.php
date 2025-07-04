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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_student')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('id_course')
                ->constrained('courses')
                ->cascadeOnDelete();
            $table->foreignId('id_schoolyear')
                ->constrained('schoolyears')
                ->cascadeOnDelete()
                ->default(null);
            $table->foreignId('id_class')
                ->constrained('classes')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};

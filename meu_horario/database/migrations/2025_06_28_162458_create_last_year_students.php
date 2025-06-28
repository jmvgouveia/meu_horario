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
            $table->id('id')->primary();
            $table->integer('id_teacher');
            $table->integer('id_subject');
            $table->integer('id_student');
            $table->integer('id_schoolyear');
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

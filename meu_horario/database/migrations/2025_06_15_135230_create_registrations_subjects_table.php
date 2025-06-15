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
        Schema::create('registrations_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_registration')
                ->constrained('registrations')
                ->cascadeOnDelete();
            $table->foreignId('id_subject')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations_subjects');
    }
};

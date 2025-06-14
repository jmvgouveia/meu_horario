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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique();
            $table->string('name');
            $table->string('acronym', 20)->unique();
            $table->date('birthdate');
            $table->date('startingdate');
            $table->foreignId('id_nationality')
                ->nullable()
                ->nullOnDelete();
            $table->foreignId('id_gender')
                ->nullable()
                ->nullOnDelete();
            $table->foreignId('id_qualification')
                ->nullable()
                ->nullOnDelete();;
            $table->foreignId('id_department')
                ->nullable()
                ->nullOnDelete();;
            $table->foreignId('id_professionalrelationship')
                ->nullable()
                ->nullOnDelete();;
            $table->foreignId('id_contractualrelationship')
                ->nullable()
                ->nullOnDelete();;
            $table->foreignId('id_salaryscale')
                ->nullable()
                ->nullOnDelete();
            $table->foreignId('id_user')
                ->nullable()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

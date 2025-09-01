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
        Schema::create('room_blocked_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_building')->index();
            $table->unsignedBigInteger('id_room')->index();
            $table->unsignedBigInteger('id_weekday')->index();
            $table->unsignedBigInteger('id_timeperiod')->index();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('id_schoolyear')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_blocked_hours');
    }
};

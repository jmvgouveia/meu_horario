<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weekdays', function (Blueprint $table) {
            $table->id();
            $table->string('weekday', 20);
            $table->timestamps();
        });

        DB::table('weekdays')->insert([
            [
                'weekday' => 'Segunda-feira',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Terça-feira',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Quarta-feira',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Quinta-feira',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Sexta-feira',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Sábado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'Domingo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekdays');
    }
};

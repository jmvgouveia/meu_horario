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
        Schema::create('timeperiods', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });

        DB::table('timeperiods')->insert([
            [
                'description' => '08:00-09:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '09:00-10:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '10:00-11:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '11:00-12:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '12:00-13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '13:00-14:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '14:00-15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '15:00-16:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '16:00-17:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '17:00-18:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '18:00-19:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '19:00-20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '20:00-21:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '21:00-22:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => '22:00-23:00',
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
        Schema::dropIfExists('timeperiods');
    }
};

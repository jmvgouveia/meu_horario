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
        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('gender', 100);
            $table->timestamps();
        });

        DB::table('genders')->insert([
            [
                'gender' => 'Masculino',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gender' => 'Feminino',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gender' => 'Outro',
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
        Schema::dropIfExists('genders');
    }
};

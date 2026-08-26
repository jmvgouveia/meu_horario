<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['number']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('number')->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['number']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->integer('number')->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('number');
        });
    }
};

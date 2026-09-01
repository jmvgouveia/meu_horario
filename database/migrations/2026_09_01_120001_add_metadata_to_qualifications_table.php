<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedTinyInteger('qnq_level')->nullable()->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('qnq_level');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn(['description', 'qnq_level', 'sort_order', 'is_active']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            if (! Schema::hasColumn('qualifications', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('qualifications', 'qnq_level')) {
                $table->unsignedTinyInteger('qnq_level')->nullable()->after('description');
            }
            if (! Schema::hasColumn('qualifications', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('qnq_level');
            }
            if (! Schema::hasColumn('qualifications', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }

            $hasUniqueName = collect(Schema::getIndexes('qualifications'))
                ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['name']);

            if (! $hasUniqueName) {
                $table->unique('name');
            }
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

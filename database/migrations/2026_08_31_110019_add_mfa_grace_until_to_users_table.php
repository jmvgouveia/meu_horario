<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('mfa_grace_until')->nullable()->after('remember_token');
            $table->timestamp('mfa_grace_renewed_at')->nullable()->after('mfa_grace_until');
            $table->foreignId('mfa_grace_renewed_by')
                ->nullable()
                ->after('mfa_grace_renewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('users')->update([
            'mfa_grace_until' => now()->addDays((int) config('two-factor.grace_days')),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mfa_grace_renewed_by');
            $table->dropColumn(['mfa_grace_until', 'mfa_grace_renewed_at']);
        });
    }
};

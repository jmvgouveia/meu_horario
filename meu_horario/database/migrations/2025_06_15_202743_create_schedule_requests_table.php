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
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_schedule')
                ->constrained('schedules')
                ->cascadeOnDelete();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->foreignId('id_new_schedule')
                ->nullable()
                ->constrained('schedules')
                ->nullOnDelete();
            $table->text('justification');
            $table->enum('status', [
                'Pendente',
                'Recusado',
                'Aprovado Professor',
                'Escalado',
                'Aprovado Coordenador',
            ])->default('Pendente');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('response_coord')->nullable();
            $table->text('scaled_justification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_requests');
    }
};

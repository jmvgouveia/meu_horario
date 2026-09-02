<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a relação many-to-many entre turmas e edifícios, migra os valores
     * antigos de classes.id_building (quando existir) e remove a coluna legada.
     */
    public function up(): void
    {
        Schema::create('class_buildings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_class')
                ->constrained('classes')
                ->cascadeOnDelete();
            $table->foreignId('id_building')
                ->constrained('buildings')
                ->restrictOnDelete();
            $table->unique(['id_class', 'id_building']);
            $table->timestamps();
        });

        if (Schema::hasColumn('classes', 'id_building')) {
            $legacyRows = DB::table('classes')
                ->whereNotNull('id_building')
                ->get(['id', 'id_building']);

            foreach ($legacyRows as $row) {
                DB::table('class_buildings')->insertOrIgnore([
                    'id_class' => $row->id,
                    'id_building' => $row->id_building,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('classes', function (Blueprint $table): void {
                $table->dropColumn('id_building');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Recria apenas a coluna legada id_building (nullable). Não restaura
     * múltiplos edifícios, uma vez que essa informação passou a viver na
     * tabela pivot class_buildings.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_buildings');

        Schema::table('classes', function (Blueprint $table): void {
            $table->integer('id_building')->nullable()->after('year');
        });
    }
};

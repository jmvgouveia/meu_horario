<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->foreignId('id_teacher_requester')
                ->nullable()
                ->after('id_teacher')
                ->constrained('teachers')
                ->nullOnDelete();
            $table->foreignId('id_schoolyear')
                ->nullable()
                ->after('id_teacher_requester')
                ->constrained('schoolyears')
                ->nullOnDelete();
        });

        DB::table('schedule_requests')
            ->select('id', 'id_schedule', 'id_teacher')
            ->orderBy('id')
            ->each(function (object $request): void {
                $conflictTeacherId = DB::table('schedules')
                    ->where('id', $request->id_schedule)
                    ->value('id_teacher');
                $schoolYearId = DB::table('schedules')
                    ->where('id', $request->id_schedule)
                    ->value('id_schoolyear');

                DB::table('schedule_requests')
                    ->where('id', $request->id)
                    ->update([
                        'id_teacher_requester' => $request->id_teacher,
                        'id_teacher' => $conflictTeacherId ?? $request->id_teacher,
                        'id_schoolyear' => $schoolYearId,
                    ]);
            });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE schedule_requests MODIFY status ENUM('Aprovado', 'Pendente', 'Recusado', 'Escalado', 'Aprovado DP', 'Recusado DP', 'Eliminado', 'Eliminado DP') NOT NULL DEFAULT 'Pendente'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE schedule_requests MODIFY status ENUM('Aprovado', 'Pendente', 'Recusado', 'Escalado', 'Aprovado DP', 'Recusado DP', 'Eliminado') NOT NULL DEFAULT 'Pendente'");
        }

        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropForeign(['id_teacher_requester']);
            $table->dropForeign(['id_schoolyear']);
            $table->dropColumn('id_teacher_requester');
            $table->dropColumn('id_schoolyear');
        });
    }
};

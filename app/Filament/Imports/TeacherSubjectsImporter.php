<?php

namespace App\Filament\Imports;

use App\Models\TeacherSubject;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;

class TeacherSubjectsImporter extends Importer
{
    protected static ?string $model = TeacherSubject::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('id_teacher')
                ->label('número de processo do professor')
                ->guess(['Docente', 'Professor', 'id_professor'])
                ->rules(['required', 'integer', 'exists:teachers,number']),

            ImportColumn::make('id_subject')
                ->label('id_disciplina')
                ->guess(['Disciplina', 'id_disciplina', 'id_subjects'])
                ->rules(['required', 'integer', 'exists:subjects,id']),

            ImportColumn::make('id_schoolyear')
                ->label('id_ano_letivo')
                ->guess(['anoletivo', 'Ano Letivo', 'id_ano_letivo'])
                ->rules(['nullable', 'integer', 'exists:schoolyears,id']),


        ];
    }

    public function resolveRecord(): ?TeacherSubject
    {
        return DB::transaction(function () {
            return new TeacherSubject();
        });
    }

    protected function beforeFill(): void
    {
        $teacherNumber = trim((string) ($this->data['id_teacher'] ?? ''));
        $this->data['id_teacher'] = Teacher::query()
            ->where('number', $teacherNumber)
            ->value('id');
        $this->data['id_subject'] = trim($this->data['id_subject'] ?? '');
        $this->data['id_schoolyear'] = SchoolYear::query()->where('active', true)->value('id');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma Disciplina foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} Disciplinas importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

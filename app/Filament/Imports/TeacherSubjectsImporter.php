<?php

namespace App\Filament\Imports;

use App\Models\TeacherSubject;
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
                ->label('Descrição')
                ->rules(['required', 'integer', 'exists:teachers,id']),

            ImportColumn::make('id_subject')
                ->label('Descrição')
                ->rules(['required', 'integer', 'exists:subjects,id']),

            ImportColumn::make('id_schoolyear')
                ->label('Descrição')
                ->rules(['required', 'integer', 'exists:schoolyears,id']),


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
        // Limpa espaços em branco
        $this->data['id_teacher'] = trim($this->data['id_teacher'] ?? '');
        $this->data['id_subject'] = trim($this->data['id_subject'] ?? '');
        $this->data['id_schoolyear'] = trim($this->data['id_schoolyear'] ?? '');
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

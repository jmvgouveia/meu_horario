<?php

namespace App\Filament\Imports;

use App\Models\CourseSubject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CourseSubjectImporter extends Importer
{
    protected static ?string $model = CourseSubject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'integer', 'exists:courses,id'])
                ->example('3'),

            ImportColumn::make('id_subject')
                ->label('ID da Disciplina')
                ->rules(['required', 'integer', 'exists:subjects,id'])
                ->example('15'),

            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Letivo')
                ->rules(['required', 'integer', 'exists:school_years,id'])
                ->example('2024'),
        ];
    }

    public function resolveRecord(): ?CourseSubject
    {
        return new CourseSubject();
    }

    protected function beforeFill(): void
    {
        $this->data['id_course'] = intval($this->data['id_course'] ?? 0);
        $this->data['id_subject'] = intval($this->data['id_subject'] ?? 0);
        $this->data['id_schoolyear'] = intval($this->data['id_schoolyear'] ?? 0);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma relação curso-disciplina foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} relações curso-disciplina importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

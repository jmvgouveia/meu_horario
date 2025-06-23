<?php

namespace App\Filament\Imports;

use App\Models\Classes;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class ClassesImporter extends Importer
{
    protected static ?string $model = Classes::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome da Turma')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    Rule::unique(Classes::class, 'name'),
                ])
                ->example('10.º A'),

            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'integer'])
                ->example('3'),

            ImportColumn::make('year')
                ->label('Ano Letivo')
                ->rules(['nullable', 'integer', 'min:1', 'max:12'])
                ->example('10'),

            ImportColumn::make('id_building')
                ->label('ID do Edifício')
                ->rules(['nullable', 'integer'])
                ->example('2'),
        ];
    }

    public function resolveRecord(): ?Classes
    {
        return new Classes();
    }

    protected function beforeFill(): void
    {
        $this->data['name'] = trim($this->data['name'] ?? '');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma turma foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} turmas importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

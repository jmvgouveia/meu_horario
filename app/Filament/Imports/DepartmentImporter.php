<?php

namespace App\Filament\Imports;

use App\Models\ContratualRelationship;
use App\Models\Department;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentImporter extends Importer
{
    protected static ?string $model = Department::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    Rule::unique(Department::class, 'name'),
                ])
                ->example('Departamento de Cordas'),

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:3',

                ])
                ->example('Departamento de Cordas'),
        ];
    }

    public function resolveRecord(): ?Department
    {
        return DB::transaction(function () {
            return new Department();
        });
    }
    protected function beforeFill(): void
    {
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['description'] = trim($this->data['description'] ?? '');
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum departamento foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} departamentos importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

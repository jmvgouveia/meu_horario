<?php

namespace App\Filament\Imports;

use App\Models\Classes;
use App\Models\Position;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PositionImporter extends Importer
{
    protected static ?string $model = Position::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome do Cargo')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    Rule::unique(Position::class, 'name'),
                ])
                ->example('Assessor Artístico'),

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['required', 'string', 'max:255', 'min:3'])
                ->example('Cargo responsável por coordenar as atividades artísticas'),

            ImportColumn::make('reduction_l')
                ->label('Reducao Componente Letiva')
                ->rules(['nullable', 'integer', 'min:1', 'max:12'])
                ->example('1'),

            ImportColumn::make('reduction_nl')
                ->label('Reducao Componente Não Letiva')
                ->rules(['nullable', 'integer', 'min:1', 'max:12'])
                ->example('2'),
        ];
    }

    public function resolveRecord(): ?Position
    {
        return DB::transaction(function () {
            return new Position();
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
            return "Nenhum cargo foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} cargos importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

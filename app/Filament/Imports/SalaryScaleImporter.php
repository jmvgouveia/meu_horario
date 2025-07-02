<?php

namespace App\Filament\Imports;


use App\Models\SalaryScale;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalaryScaleImporter extends Importer
{
    protected static ?string $model = SalaryScale::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('scale')
                ->label('Nome')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    Rule::unique(SalaryScale::class, 'scale'),
                ])
                ->example('1. Escalão'),


        ];
    }

    public function resolveRecord(): ?SalaryScale
    {
        return DB::transaction(function () {
            return new SalaryScale();
        });
    }
    protected function beforeFill(): void
    {
        $this->data['scale'] = trim($this->data['scale'] ?? '');
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum Escalão Salarial foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} Escalões Salariais importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

<?php

namespace App\Filament\Imports;

use App\Models\TimePeriod;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;

class TimePeriodImporter extends Importer
{
    protected static ?string $model = TimePeriod::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['required', 'string', 'max:255']),

        ];
    }

    public function resolveRecord(): ?TimePeriod
    {
        return DB::transaction(function () {
            return new TimePeriod();
        });
    }

    protected function beforeFill(): void
    {
        $this->data['description'] = trim($this->data['description'] ?? '');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma hora foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} horas importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

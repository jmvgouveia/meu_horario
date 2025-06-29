<?php

namespace App\Filament\Imports;

use App\Models\Nationality;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NationalityImporter extends Importer
{
    protected static ?string $model = Nationality::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nacionalidade')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    Rule::unique(Nationality::class, 'name'),
                ])
                ->example('Portuguesa'),

            ImportColumn::make('acronym')
                ->label('Sigla')
                ->rules([
                    'required',
                    'string',
                    'size:2',
                    Rule::unique(Nationality::class, 'acronym'),
                ])
                ->example('PT'),
        ];
    }

    public function resolveRecord(): ?Nationality
    {
        return DB::transaction(function () {
            return new Nationality();
        });
    }

    protected function beforeFill(): void
    {
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['acronym'] = strtoupper(trim($this->data['acronym'] ?? ''));
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma nacionalidade foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} nacionalidades importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

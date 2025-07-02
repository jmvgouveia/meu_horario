<?php

namespace App\Filament\Imports;

use App\Models\Building;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BuildingImporter extends Importer
{
    protected static ?string $model = Building::class;

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
                    Rule::unique(Building::class, 'name'),
                ])
                ->example('Edifício Central'),

            ImportColumn::make('address')
                ->label('Morada')
                ->rules([
                    'required',
                    'string',
                    'max:65535',
                ])
                ->example('Rua das Flores, 123, Lisboa'),
        ];
    }

    public function resolveRecord(): ?Building
    {
        return DB::transaction(function () {
            return new Building();
        });
    }
    protected function beforeFill(): void
    {
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['address'] = trim($this->data['address'] ?? '');
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum edifício foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} edifícios importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

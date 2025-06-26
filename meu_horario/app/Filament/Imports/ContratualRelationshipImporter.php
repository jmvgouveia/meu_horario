<?php

namespace App\Filament\Imports;

use App\Models\ContratualRelationship;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContratualRelationshipImporter extends Importer
{
    protected static ?string $model = ContratualRelationship::class;

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
                    Rule::unique(ContratualRelationship::class, 'name'),
                ])
                ->example('Edifício Central'),


        ];
    }

    public function resolveRecord(): ?ContratualRelationship
    {

        return DB::transaction(function () {
            return new ContratualRelationship();
        });
    }
    protected function beforeFill(): void
    {
        // Limpa espaços em branco
        $this->data['name'] = trim($this->data['name'] ?? '');
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum relação contratual foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} Relações contratuais importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

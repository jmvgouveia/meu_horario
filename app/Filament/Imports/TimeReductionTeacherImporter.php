<?php

namespace App\Filament\Imports;

use App\Models\TimeReduction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;

class TimeReductionTeacherImporter extends Importer
{
    protected static ?string $model = TimeReduction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Reducão')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('value_l')
                ->label('Redução Componente Letiva')
                ->rules(['nullable', 'integer', 'min:1', 'max:12'])
                ->example('1'),
            ImportColumn::make('value_nl')
                ->label('Redução Componente Não Letiva')
                ->rules(['nullable', 'integer', 'min:1', 'max:12'])
                ->example('2'),
            ImportColumn::make('eligibility')
                ->label('Elegibilidade')
                ->rules(['required', 'string', 'max:255'])
                ->example('Feminino ou Masculino'),


        ];
    }

    public function resolveRecord(): ?TimeReduction
    {
        return DB::transaction(function () {
            return new TimeReduction();
        });
    }
    protected function beforeFill(): void
    {
        // Limpa espaços em branco
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['description'] = trim($this->data['description'] ?? '');
        $this->data['eligibility'] = trim($this->data['eligibility'] ?? '');
        $this->data['value_l'] = trim($this->data['value_l'] ?? '');
        $this->data['value_nl'] = trim($this->data['value_nl'] ?? '');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma redução foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} reduções importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

<?php

namespace App\Filament\Imports;

use App\Models\Timeperiod;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TimePeriodImporter extends Importer
{
    protected static ?string $model = Timeperiod::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('start_time')
                ->label('Hora de Início')
                ->rules(['required', 'date_format:H:i']),
            ImportColumn::make('end_time')
                ->label('Hora de Fim')
                ->rules(['required', 'date_format:H:i']),
            ImportColumn::make('active')
                ->label('Ativo')
                ->castStateUsing(fn (mixed $state): mixed => self::normalizeBoolean($state))
                ->rules([
                    'required',
                    Rule::in([
                        '0', '1', 'true', 'false', 'True', 'False',
                        'sim', 'Sim', 'não', 'Não', 'nao', 'Nao',
                        'yes', 'Yes', 'no', 'No',
                    ]),
                ]),

        ];
    }

    public function resolveRecord(): ?Timeperiod
    {
        return DB::transaction(function () {
            return new Timeperiod;
        });
    }

    protected function beforeFill(): void
    {
        $this->data['description'] = trim($this->data['description'] ?? '');
        $this->data['start_time'] = trim($this->data['start_time'] ?? '');
        $this->data['end_time'] = trim($this->data['end_time'] ?? '');
        $this->data['active'] = filter_var($this->data['active'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private static function normalizeBoolean(mixed $value): mixed
    {
        return match (mb_strtolower(trim((string) $value))) {
            'true', '1', 'sim', 'yes' => 1,
            'false', '0', 'nao', 'não', 'no' => 0,
            default => $value,
        };
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

<?php

namespace App\Filament\Imports;

use App\Models\Classes;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
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

            ImportColumn::make('id_edificio')
                ->label('IDs dos Edifícios Permitidos')
                ->rules(['nullable', 'string'])
                ->fillRecordUsing(function (Classes $record, ?string $state): void {
                    // Este campo pertence à pivot class_buildings, não à tabela classes.
                })
                ->example('1|2'),

            ImportColumn::make('id_buildings')
                ->label('IDs dos Edifícios Permitidos (alias)')
                ->rules(['nullable', 'string'])
                ->fillRecordUsing(function (Classes $record, ?string $state): void {
                    // Compatibilidade com templates que usam o nome técnico.
                })
                ->example('1|2'),

        ];
    }

    public function resolveRecord(): ?Classes
    {
        return DB::transaction(function () {
            return new Classes;
        });
    }

    protected function beforeFill(): void
    {
        $this->data['name'] = trim($this->data['name'] ?? '');

        if (empty($this->data['id_edificio'] ?? null) && ! empty($this->data['id_buildings'] ?? null)) {
            $this->data['id_edificio'] = $this->data['id_buildings'];
        }

        if (empty($this->data['id_edificio'] ?? null) && ! empty($this->data['id_building'] ?? null)) {
            $this->data['id_edificio'] = $this->data['id_building'];
        }
    }

    protected function afterSave(): void
    {
        $raw = $this->data['id_edificio'] ?? $this->data['id_buildings'] ?? null;

        if ($raw === null || $raw === '') {
            $this->record->buildings()->detach();

            return;
        }

        $ids = collect(preg_split('/[|,]/', (string) $raw))
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            $this->record->buildings()->detach();

            return;
        }

        $existingIds = DB::table('buildings')->whereIn('id', $ids)->pluck('id')->all();

        if (count($existingIds) !== count($ids)) {
            throw new \Exception('Um ou mais IDs de edifício são inválidos.');
        }

        $this->record->buildings()->sync($ids);
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

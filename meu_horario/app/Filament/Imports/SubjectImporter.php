<?php

namespace App\Filament\Imports;

use App\Models\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SubjectImporter extends Importer
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Disciplina')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('acronym')
                ->label('Abreviatura')
                ->rules(['required', 'string', 'max:65535']),
        ];
    }

    public function resolveRecord(): ?Subject
    {
        return new Subject();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'name' => $data['name'],
                'acronym' => $data['acronym'],
            ]);

            $record->save();

            $import->increment('processed_rows');
            $import->increment('successful_rows');
        } catch (\Exception $e) {
            $import->increment('processed_rows');
            $import->increment('failed_rows');

            throw $e;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Importados com sucesso {$count} disciplinas.";
    }
}

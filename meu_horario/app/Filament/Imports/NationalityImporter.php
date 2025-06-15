<?php

namespace App\Filament\Imports;

use App\Models\Nationality;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class NationalityImporter extends Importer
{
    protected static ?string $model = Nationality::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nacionalidade')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('acronym')
                ->label('Sigla')
                ->rules(['required', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Nationality
    {
        return new Nationality();
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
        return "{$count} Nacionalidades Importadas com sucesso!";
    }
}

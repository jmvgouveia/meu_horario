<?php

namespace App\Filament\Imports;

use App\Models\Building;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BuildingImporter extends Importer
{
    protected static ?string $model = Building::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('address')
                ->label('Morada')
                ->rules(['required', 'string', 'max:65535']),
        ];
    }

    public function resolveRecord(): ?Building
    {
        // return Building::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Building();
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
                'address' => $data['address'],
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
        $body = 'Your building import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

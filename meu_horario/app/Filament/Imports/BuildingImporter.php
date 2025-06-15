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
        $count = $import->successful_rows;
        return "Importados com sucesso {$count} edifícios.";
    }
}

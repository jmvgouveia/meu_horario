<?php

namespace App\Filament\Imports;

use App\Models\Room;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;

class RoomImporter extends Importer
{
    protected static ?string $model = Room::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_building')
                ->label('ID do Edifício')
                ->rules(['required', 'int']),
        ];
    }

    public function resolveRecord(): ?Room
    {
        return DB::transaction(function () {
            return new Room();
        });
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
                'id_building' => $data['id_building'],
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
        $body = 'Your room import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

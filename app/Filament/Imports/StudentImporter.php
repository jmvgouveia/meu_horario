<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('number')
                ->label('Número de Estudante')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('birthdate')
                ->label('Data de Nascimento')
                ->rules(['required', 'date']),
            ImportColumn::make('id_gender')
                ->label('Género')
                ->rules(['required', 'integer']),
            ImportColumn::make('email')
                ->label('Email')
                ->fillRecordUsing(function ($record, $state, $data) {
                    // Usa o número importado e cria o email automaticamente
                    $record->email = $data['number'] . '@ceam.com';
                })
                ->rules(['required', 'email']),
        ];
    }

    public function resolveRecord(): ?Student
    {
        return DB::transaction(function () {
            return new Student();
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
                'number' => $data['number'],
                'name' => $data['name'],
                'birthdate' => $data['birthdate'],
                'id_gender' => $data['id_gender'],
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
        return "{$count} Alunos Importados com sucesso.";
    }
}

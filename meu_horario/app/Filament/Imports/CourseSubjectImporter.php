<?php

namespace App\Filament\Imports;

use App\Models\CourseSubject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CourseSubjectImporter extends Importer
{
    protected static ?string $model = CourseSubject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_course')
            ->label('ID do Curso')
            ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_subject')
                ->label('ID da Disciplina')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Letivo')
                ->rules(['required', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ?CourseSubject
    {
        return new CourseSubject();
    }

    public function import(array $data, Import $import): void
    {
        try {
            $record = $this->resolveRecord();

            if ($record === null) {
                return;
            }

            $record->fill([
                'id_course' => $data['id_course'],
                'id_subject' => $data['id_subject'],
                'id_schoolyear' => $data['id_schoolyear'],
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
        return "{$count} Relacos importadas com sucesso.";
    }
}

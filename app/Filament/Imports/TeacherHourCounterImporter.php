<?php

namespace App\Filament\Imports;

use App\Models\TeacherHourCounter;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherHourCounterImporter extends Importer
{
    protected static ?string $model = TeacherHourCounter::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('teacher_id')
                ->label('ID do Professor')
                ->rules(['required', 'exists:teachers,id'])
                ->example('1'),
            ImportColumn::make('workload')
                ->label('Carga Horária')
                ->rules(['required', 'integer', 'min:1'])
                ->example('40'),
            ImportColumn::make('teaching_load   ')
                ->label('Horas de Componente Letivo')
                ->rules(['required', 'integer', 'min:0'])
                ->example('30'),
            ImportColumn::make('non_teaching_load')
                ->label('Horas de Componente Não Letivo')
                ->rules(['required', 'integer', 'min:0'])
                ->example('10'),
            ImportColumn::make('authorized_overtime')
                ->label('Horas Extras Autorizadas')
                ->rule(Rule::in([1, 2])),
        ];
    }

    protected function beforeFill(): void
    {

        $this->data['teacher_id'] = trim($this->data['teacher_id'] ?? '');
        $this->data['workload'] = trim($this->data['workload'] ?? '');
        $this->data['teaching_load'] = trim($this->data['teaching_load'] ?? '');
        $this->data['non_teaching_load'] = trim($this->data['non_teaching_load'] ?? '');
        $this->data['authorized_overtime'] = trim($this->data['authorized_overtime'] ?? '');
    }

    public function resolveRecord(): ?TeacherHourCounter
    {
        return DB::transaction(function () {

            return new TeacherHourCounter([
                'teacher_id' => $this->data['teacher_id'] ?? null,
                'workload' => $this->data['workload'] ?? 0,
                'teaching_load' => $this->data['teaching_load'] ?? 0,
                'non_teaching_load' => $this->data['non_teaching_load'] ?? 0,
                'authorized_overtime' => $this->data['authorized_overtime'] ?? 0,
            ]);
        });
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum Carga Horaria foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} Cargas Horárias importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

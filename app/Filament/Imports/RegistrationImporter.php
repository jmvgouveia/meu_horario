<?php

namespace App\Filament\Imports;

use App\Models\Registration;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationImporter extends Importer
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id_student')
                ->label('ID do Aluno')
                ->rules(['required', 'integer', 'exists:students,id'])
                ->example('5'),

            ImportColumn::make('id_course')
                ->label('ID do Curso')
                ->rules(['required', 'integer', 'exists:courses,id'])
                ->example('3'),

            ImportColumn::make('id_schoolyear')
                ->label('ID do Ano Letivo')
                ->rules(['required', 'integer', 'exists:schoolyears,id'])
                ->example('2024'),

            ImportColumn::make('id_class')
                ->label('ID da Turma')
                ->rules(['required', 'integer', 'exists:classes,id'])
                ->example('12'),

            ImportColumn::make('id_subjects')
                ->label('IDs das Disciplinas (separados por vírgulas, ponto e vírgula ou espaços)')
                ->rules(['required', 'string'])
                ->example('59;144')
                ->fillRecordUsing(null), // ⚠️ Impede que Laravel tente gravar esta coluna
        ];
    }

    protected function beforeFill(): void
    {
        // ⚠️ Remove o campo que não pertence à tabela
        unset($this->data['id_subjects']);

        $this->data['id_student'] = intval($this->data['id_student'] ?? 0);
        $this->data['id_course'] = intval($this->data['id_course'] ?? 0);
        $this->data['id_schoolyear'] = intval($this->data['id_schoolyear'] ?? 0);
        $this->data['id_class'] = intval($this->data['id_class'] ?? 0);
    }

    public function resolveRecord(): ?Registration
    {
        return DB::transaction(function () {
            try {
                Log::debug('Importando matrícula:', $this->data);

                $registration = Registration::create([
                    'id_student' => $this->data['id_student'],
                    'id_course' => $this->data['id_course'],
                    'id_schoolyear' => $this->data['id_schoolyear'],
                    'id_class' => $this->data['id_class'],
                ]);

                // ⚠️ Usa os dados originais para recuperar os subjects
                if (!empty($this->originalData['id_subjects'])) {
                    $this->attachSubjectsToRegistration($registration, $this->originalData['id_subjects']);
                }

                return $registration;
            } catch (\Exception $e) {
                Log::error('Erro ao importar matrícula:', [
                    'message' => $e->getMessage(),
                    'linha' => $this->data,
                ]);
                throw $e;
            }
        });
    }

    private function attachSubjectsToRegistration(Registration $registration, string $subjectsList): void
    {
        try {
            // Aceita ; , espaço ou tabulação como separadores
            $subjectIds = collect(preg_split('/[;,\s]+/', $subjectsList))
                ->map(fn($id) => (int) trim($id))
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (!empty($subjectIds)) {
                $registration->subjects()->attach($subjectIds);
                Log::debug('Subjects anexados:', [
                    'registration_id' => $registration->id,
                    'subject_ids' => $subjectIds,
                ]);
            } else {
                Log::warning('Sem subject IDs válidos:', [
                    'linha' => $subjectsList,
                    'registration_id' => $registration->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao anexar subjects:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma matrícula foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} matrículas importadas com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

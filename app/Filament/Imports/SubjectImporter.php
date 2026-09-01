<?php

namespace App\Filament\Imports;

use App\Models\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class SubjectImporter extends Importer
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Disciplina')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('acronym')
                ->label('Abreviatura')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('type')
                ->label('Tipo')
                ->requiredMapping()
                ->castStateUsing(function ($state) {
                    $value = mb_strtolower(
                        trim((string) $state)
                    );

                    return match ($value) {
                        'letiva',
                        'lectiva' => 'Letiva',

                        'não letiva',
                        'nao letiva',
                        'não lectiva',
                        'nao lectiva' => 'Não letiva',

                        default => trim((string) $state),
                    };
                })
                ->rules([
                    'required',
                    Rule::in([
                        'Letiva',
                        'Não letiva',
                    ]),
                ]),

            ImportColumn::make('status')
                ->label('Ativa')
                ->requiredMapping()
                ->castStateUsing(
                    fn ($state) => self::normalizeBooleanForImport($state)
                )
                ->rules([
                    'required',
                    Rule::in([0, 1, '0', '1']),
                ]),

           /*  ImportColumn::make('student_can_enroll')
                ->label('Aluno pode inscrever-se')
                ->requiredMapping()
                ->castStateUsing(
                    fn ($state) => self::normalizeBooleanForImport($state)
                )
                ->rules([
                    'required',
                    Rule::in([0, 1, '0', '1']),
                ]), */
        ];
    }

    private static function normalizeBooleanForImport(mixed $value): mixed
    {
        $value = mb_strtolower(
            trim((string) $value)
        );

        return match ($value) {
            'sim',
            'Sim',
            's',
            'yes',
            'true',
            '1',
            'ativo',
            'ativa' => 1,

            'não',
            'Não',
            'nao',
            'n',
            'no',
            'false',
            '0',
            'inativo',
            'inativa' => 0,

            default => $value,
        };
    }

    protected function beforeFill(): void
    {
        $this->data['name'] = trim(
            $this->data['name'] ?? ''
        );

        $this->data['acronym'] = trim(
            $this->data['acronym'] ?? ''
        );
    }

    public function resolveRecord(): ?Subject
    {
        return Subject::firstOrNew([
            'acronym' => $this->data['acronym'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhuma disciplina foi importada. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} "
            . ($successful === 1
                ? 'disciplina importada'
                : 'disciplinas importadas')
            . ' com sucesso';

        if ($failed > 0) {
            $message .= ", {$failed} "
                . ($failed === 1
                    ? 'falhou'
                    : 'falharam');
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}
<?php

namespace App\Filament\Imports;

use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('number')->required(),
            ImportColumn::make('name')->required(),
            ImportColumn::make('acronym')->required(),
            ImportColumn::make('birthdate')
                ->required()
                ->rule('date'),
            ImportColumn::make('startingdate')
                ->required()
                ->rule('date'),
            ImportColumn::make('id_nationality')
                ->required()
                ->rule('exists:nationalities,id'),
            ImportColumn::make('id_gender')
                ->required()
                ->rule(Rule::in([1, 2])), // 1 = Masculino, 2 = Feminino
            ImportColumn::make('id_qualification')
                ->required()
                ->rule('exists:qualifications,id'),
            ImportColumn::make('id_department')
                ->required()
                ->rule('exists:departments,id'),
            ImportColumn::make('id_professionalrelationship')
                ->required()
                ->rule('exists:professional_relationships,id'),
            ImportColumn::make('id_contractualrelationship')
                ->required()
                ->rule('exists:contractual_relationships,id'),
            ImportColumn::make('id_salaryscale')
                ->required()
                ->rule('exists:salary_scales,id'),
            ImportColumn::make('id_user')
                ->nullable()
                ->rule('exists:users,id'),
        ];
    }

    protected function beforeFill(): void
    {
        // Limpa espaços em branco
        $this->data['number'] = trim($this->data['number'] ?? '');
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['acronym'] = trim($this->data['acronym'] ?? '');
        $this->data['birthdate'] = trim($this->data['birthdate'] ?? '');
        $this->data['startingdate'] = trim($this->data['startingdate'] ?? '');
        $this->data['id_nationality'] = trim($this->data['id_nationality'] ?? '');
        $this->data['id_gender'] = trim($this->data['id_gender'] ?? '');
        $this->data['id_qualification'] = trim($this->data['id_qualification'] ?? '');
        $this->data['id_department'] = trim($this->data['id_department'] ?? '');
        $this->data['id_professionalrelationship'] = trim($this->data['id_professionalrelationship'] ?? '');
        $this->data['id_contractualrelationship'] = trim($this->data['id_contractualrelationship'] ?? '');
        $this->data['id_salaryscale'] = trim($this->data['id_salaryscale'] ?? '');
        $this->data['id_user'] = trim($this->data['id_user'] ?? '');
    }

    public function resolveRecord(): ?Teacher
    {
        return DB::transaction(function () {

            return new Teacher([
                'number' => $this->data['number'],
                'name' => $this->data['name'],
                'acronym' => $this->data['acronym'],
                'birthdate' => Carbon::createFromFormat('Y-m-d', $this->data['birthdate']),
                'startingdate' =>  Carbon::createFromFormat('Y-m-d', $this->data['startingdate']),
                'id_nationality' => $this->data['id_nationality'],
                'id_gender' => $this->data['id_gender'],
                'id_qualification' => $this->data['id_qualification'],
                'id_department' => $this->data['id_department'],
                'id_professionalrelationship' => $this->data['id_professionalrelationship'],
                'id_contractualrelationship' => $this->data['id_contractualrelationship'],
                'id_salaryscale' => $this->data['id_salaryscale'],
                'id_user' => $this->data['id_user'] ?? null,
            ]);
        });
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum Professor foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} Professores importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}

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
            ImportColumn::make('number')
                ->rules(['required']),

            ImportColumn::make('name')
                ->rules(['required']),

            ImportColumn::make('acronym')
                ->rules(['required']),

            ImportColumn::make('birthdate')
                ->rules(['required', 'date']),

            ImportColumn::make('startingdate')
                ->rules(['required', 'date']),

            ImportColumn::make('id_nationality')
                ->rules(['required', 'exists:nationalities,id']),

            ImportColumn::make('id_gender')
                ->rules(['required', Rule::in([1, 2])]), // 1 = Masculino, 2 = Feminino

            ImportColumn::make('id_qualification')
                ->rules(['required', 'exists:qualifications,id']),

            ImportColumn::make('id_department')
                ->rules(['required', 'exists:departments,id']),

            ImportColumn::make('id_professionalrelationship')
                ->rules(['required', 'exists:professional_relationships,id']),

            ImportColumn::make('id_contractualrelationship')
                ->rules(['required', 'exists:contractual_relationships,id']),

            ImportColumn::make('id_salaryscale')
                ->rules(['required', 'exists:salary_scales,id']),

            ImportColumn::make('id_user')
                ->rules(['nullable', 'exists:users,id']),
        ];
    }

    protected function beforeFill(): void
    {
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
                //'birthdate' => Carbon::createFromFormat('Y-m-d', $this->data['birthdate']),
                //'startingdate' =>  Carbon::createFromFormat('Y-m-d', $this->data['startingdate']),

                'birthdate'    => Carbon::parse($this->data['birthdate']),
                'startingdate' => Carbon::parse($this->data['startingdate']),
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

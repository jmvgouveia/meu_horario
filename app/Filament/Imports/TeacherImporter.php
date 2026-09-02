<?php

namespace App\Filament\Imports;

use App\Helpers\DatabaseHelper;
use App\Models\ContratualRelationship;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\ProfessionalRelationship;
use App\Models\Qualification;
use App\Models\SalaryScale;
use App\Models\Teacher;
use App\Models\TeacherHourCounter;
use App\Models\User;
use App\Services\UserActivationService;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('number')
                ->label('Número')
                ->rules(['required', 'integer'])
                ->example('2001'),
            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required', 'string', 'max:255'])
                ->example('Professor Exemplo'),
            ImportColumn::make('acronym')
                ->label('Sigla')
                ->rules(['required', 'string', 'max:20'])
                ->example('PE'),
            ImportColumn::make('email')
                ->label('Email (User)')
                ->rules(['required', 'email'])
                ->fillRecordUsing(function (Teacher $record, ?string $state): void {
                    // O email pertence ao User associado, não à tabela teachers.
                })
                ->example('professor@exemplo.pt'),
            ImportColumn::make('birthdate')
                ->label('Data Nascimento')
                ->rules(['required', 'date'])
                ->example('15/01/1980'),
            ImportColumn::make('startingdate')
                ->label('Data Início')
                ->rules(['required', 'date'])
                ->example('01/09/2020'),
            ImportColumn::make('id_gender')
                ->label('Género (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:genders,id'])
                ->example('Masculino'),
            ImportColumn::make('id_nationality')
                ->label('Nacionalidade (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:nationalities,id'])
                ->example('Portuguesa'),
            ImportColumn::make('id_qualification')
                ->label('Habilitação (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:qualifications,id'])
                ->example('Licenciatura'),
            ImportColumn::make('id_department')
                ->label('Departamento (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:departments,id'])
                ->example('Cordas'),
            ImportColumn::make('id_professionalrelationship')
                ->label('Relação profissional (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:professional_relationships,id'])
                ->example('Docente'),
            ImportColumn::make('id_contractualrelationship')
                ->label('Relação contratual (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:contratual_relationships,id'])
                ->example('Contrato'),
            ImportColumn::make('id_salaryscale')
                ->label('Escalão salarial (ID ou nome)')
                ->rules(['nullable', 'integer', 'exists:salary_scales,id'])
                ->example('Escala A'),
        ];
    }

    protected function beforeValidate(): void
    {
        $this->data['number'] = trim((string) ($this->data['number'] ?? ''));
        $this->data['name'] = self::clean($this->data['name'] ?? null);
        $this->data['acronym'] = mb_strtoupper(self::clean($this->data['acronym'] ?? null) ?? '');
        $this->data['email'] = self::clean($this->data['email'] ?? null);
        $this->data['birthdate'] = self::normalizeDate($this->data['birthdate'] ?? null, 'birthdate');
        $this->data['startingdate'] = self::normalizeDate($this->data['startingdate'] ?? null, 'startingdate');

        $this->data['id_gender'] = self::resolveRelation($this->data['id_gender'] ?? null, Gender::class, 'gender');
        $this->data['id_nationality'] = self::resolveRelation($this->data['id_nationality'] ?? null, Nationality::class, 'name');
        $this->data['id_qualification'] = self::resolveRelation($this->data['id_qualification'] ?? null, Qualification::class, 'name');
        $this->data['id_department'] = self::resolveRelation($this->data['id_department'] ?? null, Department::class, 'name');
        $this->data['id_professionalrelationship'] = self::resolveRelation($this->data['id_professionalrelationship'] ?? null, ProfessionalRelationship::class, 'name');
        $this->data['id_contractualrelationship'] = self::resolveRelation($this->data['id_contractualrelationship'] ?? null, ContratualRelationship::class, 'name');
        $this->data['id_salaryscale'] = self::resolveRelation($this->data['id_salaryscale'] ?? null, SalaryScale::class, 'scale');
    }

    public function resolveRecord(): ?Teacher
    {
        return Teacher::firstOrNew([
            'number' => $this->data['number'] ?? null,
        ]);
    }

    protected function beforeSave(): void
    {
        DB::transaction(function (): void {
            $teacher = $this->record;
            $email = $this->data['email'];
            $user = $teacher->user;

            if ($user && $user->email !== $email) {
                $emailOwner = User::where('email', $email)
                    ->where($user->getKeyName(), '!=', $user->getKey())
                    ->exists();

                if ($emailOwner) {
                    throw ValidationException::withMessages([
                        'email' => 'O email já está associado a outro utilizador.',
                    ]);
                }

                $user->forceFill(['email' => $email, 'name' => $this->data['name']])->save();
            } else {
                $user ??= User::firstOrNew(['email' => $email]);

                if (! $user->exists) {
                    $user->forceFill([
                        'name' => $this->data['name'],
                        'password' => str()->random(40),
                        'is_active' => false,
                    ])->save();
                }
            }

            if (! $user->hasRole('Professor')) {
                $user->assignRole('Professor');
            }

            if (! $user->is_active && blank($user->activation_token)) {
                app(UserActivationService::class)->issueAndNotify($user);
            }

            $teacher->id_user = $user->getKey();
        });
    }

    protected function afterSave(): void
    {
        $schoolYearId = DatabaseHelper::getIDActiveSchoolyear();

        if ($schoolYearId) {
            TeacherHourCounter::firstOrCreate(
                [
                    'id_teacher' => $this->record->getKey(),
                    'id_schoolyear' => $schoolYearId,
                ],
                [
                    'workload' => 26,
                    'teaching_load' => 22,
                    'non_teaching_load' => 4,
                    'authorized_overtime' => 0,
                ],
            );
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum professor foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} professores importados/atualizados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        return $message." de {$total} registos processados.";
    }

    private static function clean(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function normalizeDate(mixed $value, string $column): string
    {
        $value = self::clean($value);

        foreach (['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'd/m/Y H:i:s', 'Y-m-d H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // Try the next supported format.
            }
        }

        throw ValidationException::withMessages([
            $column => "A data na coluna {$column} deve estar num formato válido.",
        ]);
    }

    private static function resolveRelation(mixed $value, string $model, string $labelColumn): ?int
    {
        $value = self::clean($value);

        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $normalized = Str::lower(Str::ascii($value));
        $record = $model::query()->get(['id', $labelColumn])->first(
            fn (Model $record): bool => Str::lower(Str::ascii((string) $record->{$labelColumn})) === $normalized,
        );

        if ($record) {
            return $record->getKey();
        }

        throw ValidationException::withMessages([
            'id_'.Str::snake(class_basename($model)) => "Valor de relação inválido: {$value}.",
        ]);
    }
}

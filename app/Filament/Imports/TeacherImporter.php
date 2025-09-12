<?php

namespace App\Filament\Imports;

use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('number')
                ->label('Número')
                ->rules(['required']),

            ImportColumn::make('name')
                ->label('Nome')
                ->rules(['required']),

            ImportColumn::make('acronym')
                ->label('Sigla')
                ->rules(['required']),

            ImportColumn::make('email')
                ->label('Email (User)')
                ->rules(['required', 'email'])
                ->fillRecordUsing(function (\App\Models\Teacher $record, ?string $state): void {
                    // Intencionalmente vazio: NÃO mapear para o modelo Teacher
                }),

            ImportColumn::make('birthdate')
                ->label('Data Nascimento')
                ->rules(['required']),

            ImportColumn::make('startingdate')
                ->label('Data Início')
                ->rules(['required']),
        ];
    }

    protected function beforeFill(): void
    {
        $this->data['number']       = self::clean($this->data['number'] ?? '');
        $this->data['name']         = self::clean($this->data['name'] ?? '');
        $this->data['acronym']      = self::clean($this->data['acronym'] ?? '');
        $this->data['email']        = self::clean($this->data['email'] ?? '');

        // Normaliza datas para Y-m-d (aceita d-m-Y, d/m/Y, d.m.Y, Y-m-d)
        $this->data['birthdate']    = self::normalizeDate($this->data['birthdate'] ?? null, 'birthdate');
        $this->data['startingdate'] = self::normalizeDate($this->data['startingdate'] ?? null, 'startingdate');

        // Ajustes de apresentação
        if ($this->data['acronym'] !== null) {
            $this->data['acronym'] = mb_strtoupper($this->data['acronym']);
        }
        if ($this->data['name'] !== null) {
            $this->data['name'] = trim(preg_replace('/\s+/', ' ', $this->data['name']));
        }
    }

    public function resolveRecord(): ?Teacher
    {
        return DB::transaction(function () {
            // 1) Criar/obter o User pelo email
            $email = $this->data['email'];
            if (!$email) {
                throw new \InvalidArgumentException('Email é obrigatório para criar o utilizador.');
            }

            /** @var \App\Models\User $user */
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $this->data['name'] ?? $email,
                    'password' => Hash::make(($this->data['number'] ?? 'temp') . 'CEAM'), // ex.: 542231CEAM
                ]
            );

            // atribuir role professor (Spatie)
            if (!$user->hasRole('professor')) {
                $user->assignRole('professor');
                $user->assignRole('editarcontaprofessor');
            }

            // 2) Upsert do Teacher pelo number e ligação ao user_id
            $teacher = Teacher::firstOrNew([
                'number' => $this->data['number'],
            ]);

            $teacher->fill([
                'name'         => $this->data['name'],
                'acronym'      => $this->data['acronym'],
                'birthdate'    => $this->data['birthdate'],    // Y-m-d
                'startingdate' => $this->data['startingdate'], // Y-m-d
                'id_user'      => $user->id,
            ]);

            return $teacher;
        });
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed     = $import->failed_rows;
        $total      = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum Professor foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $msg = "Importação concluída: {$successful} Professores importados/atualizados com sucesso";
        if ($failed > 0) $msg .= ", {$failed} falharam";
        $msg .= " de {$total} registos processados.";

        return $msg;
    }

    /* ====================== Helpers ====================== */

    private static function clean(?string $value): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        return $v === '' ? null : $v;
    }

    private static function normalizeDate(?string $value, string $column): ?string
    {
        $value = self::clean($value);
        if ($value === null) {
            throw new \InvalidArgumentException("A coluna '{$column}' é obrigatória.");
        }

        $formats = ['d-m-Y', 'd/m/Y', 'd.m.Y', 'Y-m-d'];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt && $dt->format($fmt) === $value) {
                    return $dt->toDateString(); // Y-m-d
                }
            } catch (\Throwable $e) {
            }
        }

        $ts = strtotime($value);
        if ($ts !== false) return date('Y-m-d', $ts);

        throw new \InvalidArgumentException(
            "Data inválida na coluna '{$column}': '{$value}'. Use d-m-Y, d/m/Y, d.m.Y ou Y-m-d."
        );
    }
}

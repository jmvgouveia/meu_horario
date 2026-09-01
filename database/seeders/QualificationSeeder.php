<?php

namespace Database\Seeders;

use App\Models\Qualification;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $qualifications = [
            [
                'name' => '1.º Ciclo do Ensino Básico',
                'description' => 'Ensino básico correspondente ao 1.º ao 4.º ano.',
                'qnq_level' => null,
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => '2.º Ciclo do Ensino Básico',
                'description' => 'Ensino básico correspondente ao 5.º e 6.º ano.',
                'qnq_level' => 1,
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'name' => '3.º Ciclo do Ensino Básico',
                'description' => 'Ensino básico correspondente ao 7.º ao 9.º ano.',
                'qnq_level' => 2,
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Ensino Secundário',
                'description' => 'Ensino secundário geral ou científico-humanístico correspondente ao 10.º, 11.º e 12.º ano.',
                'qnq_level' => 3,
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Ensino Secundário - Dupla Certificação',
                'description' => 'Ensino secundário com qualificação profissional, incluindo cursos profissionais.',
                'qnq_level' => 4,
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Ensino Pós-Secundário Não Superior',
                'description' => 'Formação pós-secundária não superior correspondente ao nível 5 do QNQ.',
                'qnq_level' => 5,
                'sort_order' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'CTeSP',
                'description' => 'Curso Técnico Superior Profissional de ensino superior de ciclo curto, sem atribuição de grau académico.',
                'qnq_level' => 5,
                'sort_order' => 70,
                'is_active' => true,
            ],
            [
                'name' => 'Bacharelato',
                'description' => 'Grau académico do sistema de ensino superior anterior ao Processo de Bolonha.',
                'qnq_level' => 6,
                'sort_order' => 80,
                'is_active' => true,
            ],
            [
                'name' => 'Licenciatura',
                'description' => 'Grau académico de licenciado, correspondente ao 1.º ciclo do ensino superior.',
                'qnq_level' => 6,
                'sort_order' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Mestrado',
                'description' => 'Grau académico de mestre, correspondente ao 2.º ciclo do ensino superior.',
                'qnq_level' => 7,
                'sort_order' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Doutoramento',
                'description' => 'Grau académico de doutor, correspondente ao 3.º ciclo do ensino superior.',
                'qnq_level' => 8,
                'sort_order' => 110,
                'is_active' => true,
            ],
        ];

        foreach ($qualifications as $qualification) {
            Qualification::updateOrCreate(
                ['name' => $qualification['name']],
                $qualification
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoAcademicoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('gestiones_admision')->updateOrInsert(
            ['nombre_gestion' => 'CUP FICCT 2026'],
            [
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activa',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $carreras = [
            ['codigo_carrera' => 'INF', 'nombre_carrera' => 'ING. INFORMATICA'],
            ['codigo_carrera' => 'SIS', 'nombre_carrera' => 'ING. SISTEMAS'],
            ['codigo_carrera' => 'RED', 'nombre_carrera' => 'ING. REDES Y TELECOMUNICACIONES'],
            ['codigo_carrera' => 'ROB', 'nombre_carrera' => 'ROBOTICA'],
        ];

        foreach ($carreras as $carrera) {
            DB::table('carreras')->updateOrInsert(
                ['codigo_carrera' => $carrera['codigo_carrera']],
                [
                    'nombre_carrera' => $carrera['nombre_carrera'],
                    'estado_activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $materias = ['Computacion', 'Matematicas', 'Ingles', 'Fisica'];

        foreach ($materias as $materia) {
            DB::table('materias')->updateOrInsert(
                ['nombre_materia' => $materia],
                [
                    'estado_activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $gestion = DB::table('gestiones_admision')->where('nombre_gestion', 'CUP FICCT 2026')->first();

        if ($gestion) {
            DB::table('capacidades_aula')->updateOrInsert(
                ['id_gestion' => $gestion->id_gestion],
                [
                    'max_estudiantes' => 70,
                    'descripcion' => 'Capacidad maxima oficial por grupo CUP.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            foreach (DB::table('carreras')->get() as $carrera) {
                DB::table('cupos_carrera')->updateOrInsert(
                    [
                        'id_gestion' => $gestion->id_gestion,
                        'id_carrera' => $carrera->id_carrera,
                    ],
                    [
                        'cupos_totales' => 100,
                        'cupos_ocupados' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $requisitos = [
                ['nombre_requisito' => 'Documento de identidad', 'descripcion' => 'Carnet de identidad vigente del interesado.'],
                ['nombre_requisito' => 'Titulo de bachiller', 'descripcion' => 'Titulo de bachiller o documento equivalente segun convocatoria.'],
            ];

            foreach ($requisitos as $requisito) {
                DB::table('requisitos')->updateOrInsert(
                    [
                        'id_gestion' => $gestion->id_gestion,
                        'nombre_requisito' => $requisito['nombre_requisito'],
                    ],
                    [
                        'descripcion' => $requisito['descripcion'],
                        'obligatorio' => true,
                        'estado_activo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}

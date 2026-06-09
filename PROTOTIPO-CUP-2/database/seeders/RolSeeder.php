<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre_rol' => 'administrador',
                'descripcion' => 'Control total del sistema CUP.',
                'permisos_json' => [
                    'seguridad.*',
                    'prepostulacion.*',
                    'pagos.*',
                    'postulantes.*',
                    'evaluacion.*',
                    'admision.*',
                    'logistica.*',
                    'docentes.*',
                    'reportes.*',
                    'dashboard.*',
                    'bitacora.*',
                ],
            ],
            [
                'nombre_rol' => 'coordinador_academico',
                'descripcion' => 'Gestiona evaluaciones, grupos, docentes y reportes academicos.',
                'permisos_json' => [
                    'evaluacion.*',
                    'admision.*',
                    'logistica.*',
                    'docentes.*',
                    'reportes.ver',
                    'dashboard.ver',
                ],
            ],
            [
                'nombre_rol' => 'docente',
                'descripcion' => 'Registra notas, asistencia y consulta su carga horaria.',
                'permisos_json' => [
                    'docente.carga_horaria.ver',
                    'docente.notas.registrar',
                    'docente.asistencia.registrar',
                ],
            ],
            [
                'nombre_rol' => 'prepostulante',
                'descripcion' => 'Presenta requisitos, paga inscripcion y completa datos faltantes.',
                'permisos_json' => [
                    'prepostulante.requisitos.presentar',
                    'prepostulante.pago.registrar',
                    'prepostulante.registro.completar',
                ],
            ],
            [
                'nombre_rol' => 'postulante_oficial',
                'descripcion' => 'Consulta datos, asistencia, notas y resultado final.',
                'permisos_json' => [
                    'postulante.datos.ver',
                    'postulante.asistencia.ver',
                    'postulante.notas.ver',
                    'postulante.resultado.ver',
                ],
            ],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre_rol' => $rol['nombre_rol']],
                [
                    'descripcion' => $rol['descripcion'],
                    'permisos_json' => json_encode($rol['permisos_json']),
                    'estado_activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

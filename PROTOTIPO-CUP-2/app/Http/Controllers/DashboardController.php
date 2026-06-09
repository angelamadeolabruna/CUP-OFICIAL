<?php

namespace App\Http\Controllers;

use App\Models\Admision;
use App\Models\Carrera;
use App\Models\GestionAdmision;
use App\Models\Grupo;
use App\Models\Postulante;
use App\Models\Resultado;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $gestiones = GestionAdmision::orderByDesc('fecha_inicio')->get();
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();
        $carreras = Carrera::where('estado_activo', true)->orderBy('nombre_carrera')->get();

        $gestionId = $request->filled('id_gestion')
            ? (int) $request->id_gestion
            : ($gestionActiva?->id_gestion);

        $carreraId = $request->filled('id_carrera') ? (int) $request->id_carrera : null;

        // Query base
        $gestionLabel = $gestiones->firstWhere('id_gestion', $gestionId)?->nombre_gestion ?? '—';

        // Postulantes en la gestión
        $postulantesQuery = Postulante::where('id_gestion', $gestionId);
        if ($carreraId) {
            $postulantesQuery->where(function ($q) use ($carreraId) {
                $q->where('carrera_primera_opcion', $carreraId)
                    ->orWhere('carrera_segunda_opcion', $carreraId);
            });
        }

        $totalPostulantes = (clone $postulantesQuery)->count();
        $totalAprobados = (clone $postulantesQuery)
            ->whereHas('resultado', fn($q) => $q->where('estado_academico', 'aprobado'))->count();
        $totalReprobados = (clone $postulantesQuery)
            ->whereHas('resultado', fn($q) => $q->where('estado_academico', 'reprobado'))->count();
        $sinResultado = $totalPostulantes - $totalAprobados - $totalReprobados;

        $totalAdmitidos = Admision::where('estado_admision', 'admitido')
            ->whereHas('postulante', function ($q) use ($gestionId, $carreraId) {
                $q->where('id_gestion', $gestionId);
                if ($carreraId) {
                    $q->where(function ($qq) use ($carreraId) {
                        $qq->where('carrera_primera_opcion', $carreraId)
                            ->orWhere('carrera_segunda_opcion', $carreraId);
                    });
                }
            })->count();

        $totalGrupos = Grupo::where('id_gestion', $gestionId)->count();

        // Porcentajes para los gráficos
        $porcentajeAprobados = $totalPostulantes > 0
            ? round(($totalAprobados / $totalPostulantes) * 100, 1) : 0;
        $porcentajeReprobados = $totalPostulantes > 0
            ? round(($totalReprobados / $totalPostulantes) * 100, 1) : 0;

        // Estadísticas por carrera (solo sin filtro de carrera)
        $statsPorCarrera = collect();
        if (!$carreraId) {
            foreach ($carreras as $c) {
                $totalCarrera = Postulante::where('id_gestion', $gestionId)
                    ->where(function ($q) use ($c) {
                        $q->where('carrera_primera_opcion', $c->id_carrera)
                            ->orWhere('carrera_segunda_opcion', $c->id_carrera);
                    })->count();
                $aprobadosCarrera = Postulante::where('id_gestion', $gestionId)
                    ->where(function ($q) use ($c) {
                        $q->where('carrera_primera_opcion', $c->id_carrera)
                            ->orWhere('carrera_segunda_opcion', $c->id_carrera);
                    })
                    ->whereHas('resultado', fn($q) => $q->where('estado_academico', 'aprobado'))
                    ->count();

                $statsPorCarrera->push([
                    'carrera' => $c,
                    'total' => $totalCarrera,
                    'aprobados' => $aprobadosCarrera,
                    'porcentaje' => $totalCarrera > 0
                        ? round(($aprobadosCarrera / $totalCarrera) * 100, 1) : 0,
                ]);
            }
            $statsPorCarrera = $statsPorCarrera->sortByDesc('total');
        }

        return view('dashboard', compact(
            'gestiones', 'gestionActiva', 'gestionId', 'gestionLabel',
            'carreras', 'carreraId',
            'totalPostulantes', 'totalAprobados', 'totalReprobados', 'sinResultado',
            'totalAdmitidos', 'totalGrupos',
            'porcentajeAprobados', 'porcentajeReprobados',
            'statsPorCarrera',
        ));
    }
}

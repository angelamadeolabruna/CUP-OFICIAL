<?php

namespace App\Http\Controllers;

use App\Models\Admision;
use App\Models\Carrera;
use App\Models\Evaluacion;
use App\Models\GestionAdmision;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\PostulanteGrupo;
use App\Models\Prepostulante;
use App\Models\Resultado;
use App\Models\CargaHoraria;
use App\Models\Docente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    private function getGestionActiva()
    {
        return GestionAdmision::activa()->first() ?? GestionAdmision::first();
    }

    public function index()
    {
        return view('reportes.index', [
            'gestionActiva' => $this->getGestionActiva(),
        ]);
    }

    // 1. Reporte de Postulantes
    public function postulantes(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $query = Postulante::with([
            'prepostulante', 'primeraOpcion', 'segundaOpcion', 'resultado', 'admision',
        ])->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('estado_postulante')) {
            $query->where('estado_postulante', $request->estado_postulante);
        }

        if ($request->filled('id_carrera')) {
            $query->where(function ($q) use ($request) {
                $q->where('carrera_primera_opcion', $request->id_carrera)
                    ->orWhere('carrera_segunda_opcion', $request->id_carrera);
            });
        }

        $postulantes = $query
            ->orderBy(
                Prepostulante::selectRaw("apellidos")
                    ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')
                    ->limit(1)
            )->paginate(50);

        $carreras = \App\Models\Carrera::where('estado_activo', true)->orderBy('nombre_carrera')->get();

        return view('reportes.postulantes', compact('gestionActiva', 'postulantes', 'carreras'));
    }

    // 2 y 3. Reporte de Aprobados / Reprobados
    public function resultadosAcademicos(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $query = Postulante::with([
            'prepostulante', 'primeraOpcion', 'segundaOpcion', 'resultado', 'admision',
        ])->whereHas('resultado')
            ->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('estado')) {
            $query->whereHas('resultado', fn($q) => $q->where('estado_academico', $request->estado));
        }

        $postulantes = $query
            ->orderByDesc(
                Resultado::select('promedio_final')
                    ->whereColumn('resultados.id_postulante', 'postulantes.id_postulante')
                    ->limit(1)
            )->paginate(50);

        $conteo = [
            'aprobados' => Resultado::join('postulantes', 'resultados.id_postulante', '=', 'postulantes.id_postulante')
                ->where('postulantes.id_gestion', $gestionActiva?->id_gestion)
                ->where('resultados.estado_academico', 'aprobado')->count(),
            'reprobados' => Resultado::join('postulantes', 'resultados.id_postulante', '=', 'postulantes.id_postulante')
                ->where('postulantes.id_gestion', $gestionActiva?->id_gestion)
                ->where('resultados.estado_academico', 'reprobado')->count(),
        ];

        return view('reportes.resultados_academicos', compact('gestionActiva', 'postulantes', 'conteo'));
    }

    // 4. Reporte de Promedios por Materia
    public function promedios(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $materias = Materia::where('estado_activo', true)->orderBy('nombre_materia')->get();

        $postulantes = Postulante::with([
            'prepostulante', 'notas.evaluacion.materia',
        ])->whereHas('notas.evaluacion', fn($q) => $q->where('id_gestion', $gestionActiva?->id_gestion))
            ->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('id_materia')) {
            $postulantes->whereHas('notas.evaluacion', fn($q) => $q->where('id_materia', $request->id_materia));
        }

        $postulantes = $postulantes->orderBy(
            Prepostulante::selectRaw("apellidos")
                ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')
                ->limit(1)
        )->paginate(50);

        $promediosPostulantes = collect();
        foreach ($postulantes as $p) {
            $notasPorMateria = $p->notas->groupBy(fn($n) => $n->evaluacion->id_materia);
            $promMaterias = [];
            foreach ($notasPorMateria as $idMateria => $notas) {
                $suma = $notas->sum(fn($n) => $n->nota * ($n->evaluacion->porcentaje / 100));
                $promMaterias[] = [
                    'materia' => $notas->first()->evaluacion->materia,
                    'promedio' => round($suma, 2),
                ];
            }
            $promediosPostulantes->push([
                'postulante' => $p,
                'promedios' => $promMaterias,
                'promedio_final' => $p->resultado?->promedio_final,
            ]);
        }

        return view('reportes.promedios', compact('gestionActiva', 'materias', 'promediosPostulantes', 'postulantes'));
    }

    // 5. Reporte de Grupos
    public function grupos(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $grupos = Grupo::with([
            'aula', 'materia', 'postulantes.postulante.prepostulante', 'horarios.horario',
        ])->where('id_gestion', $gestionActiva?->id_gestion)
            ->orderBy('nombre_grupo')
            ->paginate(20);

        return view('reportes.grupos', compact('gestionActiva', 'grupos'));
    }

    // 6. Estadísticas por materia
    public function estadisticasMateria(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $materias = Materia::where('estado_activo', true)->orderBy('nombre_materia')->get();
        $estadisticas = collect();

        foreach ($materias as $m) {
            $evaluaciones = Evaluacion::where('id_materia', $m->id_materia)
                ->where('id_gestion', $gestionActiva?->id_gestion)->get();

            $idsEval = $evaluaciones->pluck('id_evaluacion');
            $notas = Nota::whereIn('id_evaluacion', $idsEval)->with('evaluacion')->get();

            $totalPostulantes = Postulante::whereHas('notas.evaluacion', fn($q) => $q->where('id_materia', $m->id_materia))
                ->where('id_gestion', $gestionActiva?->id_gestion)->count();

            $promediosPorPostulante = $notas->groupBy('id_postulante')->map(function ($notasPost) {
                $suma = $notasPost->sum(fn($n) => $n->nota * ($n->evaluacion->porcentaje / 100));
                return round($suma, 2);
            });

            $promedioGeneral = $promediosPorPostulante->avg();

            $aprobadosMateria = $promediosPorPostulante->filter(fn($p) => $p >= 60)->count();
            $reprobadosMateria = $promediosPorPostulante->filter(fn($p) => $p < 60)->count();

            $estadisticas->push([
                'materia' => $m,
                'total_postulantes' => $totalPostulantes,
                'con_notas' => $promediosPorPostulante->count(),
                'promedio_general' => $promedioGeneral ? round($promedioGeneral, 2) : null,
                'aprobados' => $aprobadosMateria,
                'reprobados' => $reprobadosMateria,
                'porcentaje_aprobacion' => $promediosPorPostulante->count() > 0
                    ? round(($aprobadosMateria / $promediosPorPostulante->count()) * 100, 1)
                    : 0,
            ]);
        }

        return view('reportes.estadisticas_materia', compact('gestionActiva', 'estadisticas'));
    }

    // 7. Docentes por grupo
    public function docentesGrupo(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $docentes = Docente::with(['cargasHorarias.grupo', 'cargasHorarias.materia'])
            ->whereHas('cargasHorarias.grupo', fn($q) => $q->where('id_gestion', $gestionActiva?->id_gestion))
            ->orderBy('apellidos')
            ->paginate(20);

        return view('reportes.docentes_grupo', compact('gestionActiva', 'docentes'));
    }

    // 8. Grupos con más aprobados
    public function gruposMasAprobados(Request $request)
    {
        $gestionActiva = $this->getGestionActiva();

        $grupos = Grupo::with(['materia', 'aula'])
            ->where('id_gestion', $gestionActiva?->id_gestion)
            ->get();

        $ranking = collect();
        foreach ($grupos as $g) {
            $total = PostulanteGrupo::where('id_grupo', $g->id_grupo)->count();
            $aprobados = PostulanteGrupo::where('id_grupo', $g->id_grupo)
                ->whereHas('postulante.resultado', fn($q) => $q->where('estado_academico', 'aprobado'))
                ->count();

            $ranking->push([
                'grupo' => $g,
                'total' => $total,
                'aprobados' => $aprobados,
                'porcentaje' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0,
            ]);
        }

        $ranking = $ranking->sortByDesc('porcentaje')->values();

        return view('reportes.grupos_mas_aprobados', compact('gestionActiva', 'ranking'));
    }

    // CU36: Exportar Reporte
    public function exportar(Request $request, $tipo, $formato)
    {
        $gestionActiva = $this->getGestionActiva();

        if (!in_array($formato, ['pdf', 'csv'])) {
            return back()->withErrors(['error' => 'Formato no válido.']);
        }

        $data = $this->getExportData($tipo, $request, $gestionActiva);
        if (!$data) {
            return back()->withErrors(['error' => 'Tipo de reporte no válido.']);
        }

        $nombre = match ($tipo) {
            'postulantes' => 'reporte_postulantes',
            'resultados-academicos' => 'reporte_resultados_academicos',
            'promedios' => 'reporte_promedios',
            'grupos' => 'reporte_grupos',
            'estadisticas-materia' => 'reporte_estadisticas_materia',
            'docentes-grupo' => 'reporte_docentes_grupo',
            'grupos-mas-aprobados' => 'reporte_grupos_mas_aprobados',
            default => 'reporte',
        };

        $nombre .= '_' . now()->format('Ymd_His');

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView("reportes.exportar_pdf.{$tipo}", $data)
                ->setPaper('A4', 'landscape');
            return $pdf->download("{$nombre}.pdf");
        }

        // CSV
        $rows = $this->getCsvRows($tipo, $data);
        $csv = implode("\r\n", $rows);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$nombre}.csv\"",
        ]);
    }

    private function getExportData($tipo, Request $request, $gestionActiva)
    {
        return match ($tipo) {
            'postulantes' => $this->exportDataPostulantes($request, $gestionActiva),
            'resultados-academicos' => $this->exportDataResultados($request, $gestionActiva),
            'promedios' => $this->exportDataPromedios($request, $gestionActiva),
            'grupos' => $this->exportDataGrupos($gestionActiva),
            'estadisticas-materia' => $this->exportDataEstadisticas($gestionActiva),
            'docentes-grupo' => $this->exportDataDocentes($gestionActiva),
            'grupos-mas-aprobados' => $this->exportDataRanking($gestionActiva),
            default => null,
        };
    }

    private function getCsvRows($tipo, $data)
    {
        $rows = [];

        switch ($tipo) {
            case 'postulantes':
                $rows[] = 'ID,CI,Apellidos,Nombres,1ra Opcion,2da Opcion,Promedio,Estado Academico,Admision';
                foreach ($data['postulantes'] as $p) {
                    $rows[] = implode(',', [
                        $p->id_postulante,
                        '"' . ($p->prepostulante?->ci ?? '') . '"',
                        '"' . ($p->prepostulante?->apellidos ?? '') . '"',
                        '"' . ($p->prepostulante?->nombres ?? '') . '"',
                        '"' . ($p->primeraOpcion?->codigo_carrera ?? '') . '"',
                        '"' . ($p->segundaOpcion?->codigo_carrera ?? '') . '"',
                        $p->resultado?->promedio_final ?? '',
                        $p->resultado?->estado_academico ?? '',
                        $p->admision?->estado_admision ?? '',
                    ]);
                }
                break;

            case 'resultados-academicos':
                $rows[] = 'ID,CI,Apellidos,Nombres,Promedio,1ra Opcion,2da Opcion,Estado,Admision';
                foreach ($data['postulantes'] as $p) {
                    $rows[] = implode(',', [
                        $p->id_postulante,
                        '"' . ($p->prepostulante?->ci ?? '') . '"',
                        '"' . ($p->prepostulante?->apellidos ?? '') . '"',
                        '"' . ($p->prepostulante?->nombres ?? '') . '"',
                        $p->resultado?->promedio_final ?? '',
                        '"' . ($p->primeraOpcion?->codigo_carrera ?? '') . '"',
                        '"' . ($p->segundaOpcion?->codigo_carrera ?? '') . '"',
                        $p->resultado?->estado_academico ?? '',
                        $p->admision?->estado_admision ?? '',
                    ]);
                }
                break;

            case 'estadisticas-materia':
                $rows[] = 'Materia,Total Postulantes,Con Notas,Promedio General,Aprobados,Reprobados,% Aprobacion';
                foreach ($data['estadisticas'] as $e) {
                    $rows[] = implode(',', [
                        '"' . ($e['materia']->nombre_materia ?? '') . '"',
                        $e['total_postulantes'],
                        $e['con_notas'],
                        $e['promedio_general'] ?? '',
                        $e['aprobados'],
                        $e['reprobados'],
                        $e['porcentaje_aprobacion'] . '%',
                    ]);
                }
                break;

            case 'grupos-mas-aprobados':
                $rows[] = '#,Grupo,Materia,Aula,Total Postulantes,Aprobados,% Aprobacion';
                foreach ($data['ranking'] as $i => $r) {
                    $rows[] = implode(',', [
                        $i + 1,
                        '"' . $r['grupo']->nombre_grupo . '"',
                        '"' . ($r['grupo']->materia?->nombre_materia ?? '') . '"',
                        '"' . ($r['grupo']->aula?->codigo_aula ?? $r['grupo']->aula?->nombre ?? '') . '"',
                        $r['total'],
                        $r['aprobados'],
                        $r['porcentaje'] . '%',
                    ]);
                }
                break;

            default:
                $rows[] = 'Exportacion no disponible en CSV para este reporte.';
        }

        return $rows;
    }

    private function exportDataPostulantes(Request $request, $gestionActiva)
    {
        $query = Postulante::with(['prepostulante', 'primeraOpcion', 'segundaOpcion', 'resultado', 'admision'])
            ->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('estado_postulante')) {
            $query->where('estado_postulante', $request->estado_postulante);
        }
        if ($request->filled('id_carrera')) {
            $query->where(function ($q) use ($request) {
                $q->where('carrera_primera_opcion', $request->id_carrera)
                    ->orWhere('carrera_segunda_opcion', $request->id_carrera);
            });
        }

        $postulantes = $query->orderBy(
            Prepostulante::selectRaw("apellidos")
                ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')->limit(1)
        )->get();

        $carreras = Carrera::where('estado_activo', true)->orderBy('nombre_carrera')->get();
        return compact('gestionActiva', 'postulantes', 'carreras');
    }

    private function exportDataResultados(Request $request, $gestionActiva)
    {
        $query = Postulante::with(['prepostulante', 'primeraOpcion', 'segundaOpcion', 'resultado', 'admision'])
            ->whereHas('resultado')->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('estado')) {
            $query->whereHas('resultado', fn($q) => $q->where('estado_academico', $request->estado));
        }

        $postulantes = $query->orderByDesc(
            Resultado::select('promedio_final')
                ->whereColumn('resultados.id_postulante', 'postulantes.id_postulante')->limit(1)
        )->get();

        $conteo = [
            'aprobados' => Resultado::join('postulantes', 'resultados.id_postulante', '=', 'postulantes.id_postulante')
                ->where('postulantes.id_gestion', $gestionActiva?->id_gestion)
                ->where('resultados.estado_academico', 'aprobado')->count(),
            'reprobados' => Resultado::join('postulantes', 'resultados.id_postulante', '=', 'postulantes.id_postulante')
                ->where('postulantes.id_gestion', $gestionActiva?->id_gestion)
                ->where('resultados.estado_academico', 'reprobado')->count(),
        ];

        return compact('gestionActiva', 'postulantes', 'conteo');
    }

    private function exportDataPromedios(Request $request, $gestionActiva)
    {
        $materias = Materia::where('estado_activo', true)->orderBy('nombre_materia')->get();
        $postulantes = Postulante::with(['prepostulante', 'notas.evaluacion.materia'])
            ->whereHas('notas.evaluacion', fn($q) => $q->where('id_gestion', $gestionActiva?->id_gestion))
            ->where('id_gestion', $gestionActiva?->id_gestion);

        if ($request->filled('id_materia')) {
            $postulantes->whereHas('notas.evaluacion', fn($q) => $q->where('id_materia', $request->id_materia));
        }

        $postulantes = $postulantes->orderBy(
            Prepostulante::selectRaw("apellidos")
                ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')->limit(1)
        )->get();

        $promediosPostulantes = collect();
        foreach ($postulantes as $p) {
            $notasPorMateria = $p->notas->groupBy(fn($n) => $n->evaluacion->id_materia);
            $promMaterias = [];
            foreach ($notasPorMateria as $idMateria => $notas) {
                $suma = $notas->sum(fn($n) => $n->nota * ($n->evaluacion->porcentaje / 100));
                $promMaterias[] = [
                    'materia' => $notas->first()->evaluacion->materia,
                    'promedio' => round($suma, 2),
                ];
            }
            $promediosPostulantes->push([
                'postulante' => $p,
                'promedios' => $promMaterias,
                'promedio_final' => $p->resultado?->promedio_final,
            ]);
        }

        return compact('gestionActiva', 'materias', 'promediosPostulantes', 'postulantes');
    }

    private function exportDataGrupos($gestionActiva)
    {
        $grupos = Grupo::with(['aula', 'materia', 'postulantes.postulante.prepostulante', 'horarios.horario'])
            ->where('id_gestion', $gestionActiva?->id_gestion)
            ->orderBy('nombre_grupo')
            ->get();

        return compact('gestionActiva', 'grupos');
    }

    private function exportDataEstadisticas($gestionActiva)
    {
        $materias = Materia::where('estado_activo', true)->orderBy('nombre_materia')->get();
        $estadisticas = collect();

        foreach ($materias as $m) {
            $evaluaciones = Evaluacion::where('id_materia', $m->id_materia)
                ->where('id_gestion', $gestionActiva?->id_gestion)->get();
            $idsEval = $evaluaciones->pluck('id_evaluacion');
            $notas = Nota::whereIn('id_evaluacion', $idsEval)->with('evaluacion')->get();

            $totalPostulantes = Postulante::whereHas('notas.evaluacion', fn($q) => $q->where('id_materia', $m->id_materia))
                ->where('id_gestion', $gestionActiva?->id_gestion)->count();

            $promediosPorPostulante = $notas->groupBy('id_postulante')->map(function ($notasPost) {
                $suma = $notasPost->sum(fn($n) => $n->nota * ($n->evaluacion->porcentaje / 100));
                return round($suma, 2);
            });

            $promedioGeneral = $promediosPorPostulante->avg();
            $aprobadosMateria = $promediosPorPostulante->filter(fn($p) => $p >= 60)->count();
            $reprobadosMateria = $promediosPorPostulante->filter(fn($p) => $p < 60)->count();

            $estadisticas->push([
                'materia' => $m,
                'total_postulantes' => $totalPostulantes,
                'con_notas' => $promediosPorPostulante->count(),
                'promedio_general' => $promedioGeneral ? round($promedioGeneral, 2) : null,
                'aprobados' => $aprobadosMateria,
                'reprobados' => $reprobadosMateria,
                'porcentaje_aprobacion' => $promediosPorPostulante->count() > 0
                    ? round(($aprobadosMateria / $promediosPorPostulante->count()) * 100, 1) : 0,
            ]);
        }

        return compact('gestionActiva', 'estadisticas');
    }

    private function exportDataDocentes($gestionActiva)
    {
        $docentes = Docente::with(['cargasHorarias.grupo', 'cargasHorarias.materia'])
            ->whereHas('cargasHorarias.grupo', fn($q) => $q->where('id_gestion', $gestionActiva?->id_gestion))
            ->orderBy('apellidos')
            ->get();

        return compact('gestionActiva', 'docentes');
    }

    private function exportDataRanking($gestionActiva)
    {
        $grupos = Grupo::with(['materia', 'aula'])
            ->where('id_gestion', $gestionActiva?->id_gestion)->get();

        $ranking = collect();
        foreach ($grupos as $g) {
            $total = PostulanteGrupo::where('id_grupo', $g->id_grupo)->count();
            $aprobados = PostulanteGrupo::where('id_grupo', $g->id_grupo)
                ->whereHas('postulante.resultado', fn($q) => $q->where('estado_academico', 'aprobado'))
                ->count();
            $ranking->push([
                'grupo' => $g,
                'total' => $total,
                'aprobados' => $aprobados,
                'porcentaje' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0,
            ]);
        }

        $ranking = $ranking->sortByDesc('porcentaje')->values();
        return compact('gestionActiva', 'ranking');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Admision;
use App\Models\CargaHoraria;
use App\Models\Carrera;
use App\Models\CupoCarrera;
use App\Models\Docente;
use App\Models\Evaluacion;
use App\Models\GestionAdmision;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\PostulanteGrupo;
use App\Models\Prepostulante;
use App\Models\Resultado;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicoController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // CU18: Configurar Materias, Exámenes y Porcentajes
    public function evaluacionesIndex(Request $request)
    {
        $gestiones = GestionAdmision::orderByDesc('id_gestion')->get();

        $idGestion = $request->input('id_gestion', optional($gestiones->firstWhere('estado', 'activa'))->id_gestion ?? $gestiones->first()->id_gestion);

        $materias = Materia::where('estado_activo', true)
            ->with(['evaluaciones' => function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion)->orderBy('numero_evaluacion');
            }])
            ->orderBy('nombre_materia')
            ->get();

        if (!$idGestion) {
            return view('academico.evaluaciones', compact('gestiones', 'materias', 'idGestion'))
                ->with('status', 'No hay gestiones académicas registradas.');
        }

        return view('academico.evaluaciones', compact('gestiones', 'materias', 'idGestion'));
    }

    public function evaluacionesGuardar(Request $request)
    {
        $request->validate([
            'id_gestion' => ['required', 'exists:gestiones_admision,id_gestion'],
            'id_materia' => ['required', 'exists:materias,id_materia'],
            'numero_evaluacion' => ['required', 'integer', 'min:1', 'max:3'],
            'porcentaje' => ['required', 'numeric', 'min:1', 'max:100'],
            'fecha_evaluacion' => ['nullable', 'date'],
        ], [
            'numero_evaluacion.max' => 'Solo se permiten hasta 3 exámenes por materia.',
            'porcentaje.min' => 'El porcentaje debe ser al menos 1%.',
            'porcentaje.max' => 'El porcentaje no puede superar 100%.',
        ]);

        // Validar que no exista ya ese número de evaluación para la misma materia y gestión
        $existe = Evaluacion::where('id_gestion', $request->id_gestion)
            ->where('id_materia', $request->id_materia)
            ->where('numero_evaluacion', $request->numero_evaluacion)
            ->exists();

        if ($existe) {
            return back()->withErrors(['error' => 'El ' . $request->numero_evaluacion . '° examen ya está configurado para esta materia en la gestión seleccionada.']);
        }

        // Validar que no se excedan 3 evaluaciones
        $countActual = Evaluacion::where('id_gestion', $request->id_gestion)
            ->where('id_materia', $request->id_materia)
            ->count();

        if ($countActual >= 3) {
            return back()->withErrors(['error' => 'Ya existen 3 exámenes configurados para esta materia.']);
        }

        // Validar que la suma de porcentajes no exceda 100
        $sumaActual = Evaluacion::where('id_gestion', $request->id_gestion)
            ->where('id_materia', $request->id_materia)
            ->sum('porcentaje');

        if (($sumaActual + $request->porcentaje) > 100) {
            return back()->withErrors(['error' => 'La suma de porcentajes no puede superar 100%. Actual: ' . $sumaActual . '%, intentas agregar: ' . $request->porcentaje . '%.']);
        }

        try {
            $evaluacion = Evaluacion::create([
                'id_gestion' => $request->id_gestion,
                'id_materia' => $request->id_materia,
                'numero_evaluacion' => $request->numero_evaluacion,
                'porcentaje' => $request->porcentaje,
                'fecha_evaluacion' => $request->fecha_evaluacion,
                'estado' => 'programada',
            ]);

            $this->bitacoraService->registrar(
                Auth::id(), 'INSERT', 'evaluaciones',
                "Evaluación configurada: {$evaluacion->numero_evaluacion}° examen de materia ID {$request->id_materia} con {$request->porcentaje}%",
                $request->ip(),
                $evaluacion->id_evaluacion
            );

            return back()->with('status', 'Examen ' . $request->numero_evaluacion . '° agregado correctamente con ' . $request->porcentaje . '% de ponderación.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function evaluacionesEliminar(Request $request, $id)
    {
        $evaluacion = Evaluacion::findOrFail($id);

        DB::beginTransaction();
        try {
            $descripcion = "{$evaluacion->numero_evaluacion}° examen ({$evaluacion->porcentaje}%) de materia ID {$evaluacion->id_materia}";

            $evaluacion->delete();

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'DELETE', 'evaluaciones',
                "Evaluación eliminada: {$descripcion}",
                $request->ip(),
                $id
            );

            return back()->with('status', 'Examen eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    // CU19: Registrar Notas por Materia
    public function notasIndex(Request $request)
    {
        $rol = Auth::user()->rol?->nombre_rol;
        $docente = null;
        $grupos = collect();
        $sinRegistro = false;

        if ($rol === 'docente') {
            $docente = Docente::where('id_usuario', Auth::id())->first()
                ?? Docente::where('correo', Auth::user()->email)->first();

            if (!$docente) {
                $sinRegistro = true;
                return view('academico.notas', compact('sinRegistro'));
            }

            $docente->load('cargasHorarias.grupo.materia');
            $grupos = $docente->cargasHorarias->pluck('grupo')->filter()->unique('id_grupo')->values();
        } else {
            $grupos = Grupo::with('materia')->where('estado', 'activo')->orderBy('nombre_grupo')->get();
        }

        $idGrupo = $request->input('id_grupo');
        $idEvaluacion = $request->input('id_evaluacion');
        $grupoSeleccionado = null;
        $evaluaciones = collect();
        $evaluacionSeleccionada = null;
        $postulantes = collect();
        $notasExistentes = collect();

        if ($idGrupo) {
            $grupoSeleccionado = $grupos->firstWhere('id_grupo', (int)$idGrupo);

            if ($grupoSeleccionado) {
                $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();
                if ($gestionActiva) {
                    $evaluaciones = Evaluacion::where('id_gestion', $gestionActiva->id_gestion)
                        ->where('id_materia', $grupoSeleccionado->id_materia)
                        ->orderBy('numero_evaluacion')
                        ->get();

                    if ($idEvaluacion) {
                        $evaluacionSeleccionada = $evaluaciones->firstWhere('id_evaluacion', (int)$idEvaluacion);
                    }

                    $idsPostulantes = PostulanteGrupo::where('id_grupo', $idGrupo)
                        ->where('estado', 'activo')
                        ->pluck('id_postulante');

                    $postulantes = Postulante::with('prepostulante')
                        ->whereIn('id_postulante', $idsPostulantes)
                        ->orderBy('id_postulante')
                        ->get();

                    if ($evaluacionSeleccionada) {
                        $notasExistentes = Nota::whereIn('id_postulante', $idsPostulantes)
                            ->where('id_evaluacion', $evaluacionSeleccionada->id_evaluacion)
                            ->get()
                            ->keyBy('id_postulante');
                    }
                }
            }
        }

        return view('academico.notas', compact(
            'docente', 'grupos', 'grupoSeleccionado', 'sinRegistro',
            'evaluaciones', 'evaluacionSeleccionada',
            'postulantes', 'notasExistentes', 'rol'
        ));
    }

    public function notasStore(Request $request)
    {
        $rol = Auth::user()->rol?->nombre_rol;
        $idDocente = null;
        $docenteNombre = 'Administrador';

        if ($rol === 'docente') {
            $docente = Docente::where('id_usuario', Auth::id())->first()
                ?? Docente::where('correo', Auth::user()->email)->first();

            if (!$docente) {
                return back()->withErrors(['error' => 'No estás registrado como docente.']);
            }

            $tieneAcceso = CargaHoraria::where('id_docente', $docente->id_docente)
                ->where('id_grupo', $request->id_grupo)
                ->exists();

            if (!$tieneAcceso) {
                return back()->withErrors(['error' => 'No tienes asignado este grupo.']);
            }

            $idDocente = $docente->id_docente;
            $docenteNombre = $docente->nombre_completo;
        }

        $request->validate([
            'id_grupo' => ['required', 'exists:grupos,id_grupo'],
            'id_evaluacion' => ['required', 'exists:evaluaciones,id_evaluacion'],
            'notas' => ['required', 'array'],
            'notas.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'notas.*.required' => 'Todas las notas son obligatorias.',
            'notas.*.numeric' => 'Las notas deben ser numéricas.',
            'notas.*.min' => 'La nota mínima es 0.',
            'notas.*.max' => 'La nota máxima es 100.',
        ]);

        $guardadas = 0;

        DB::beginTransaction();
        try {
            foreach ($request->notas as $idPostulante => $notaValor) {
                Nota::updateOrCreate(
                    [
                        'id_postulante' => $idPostulante,
                        'id_evaluacion' => $request->id_evaluacion,
                    ],
                    [
                        'id_docente' => $idDocente,
                        'nota' => $notaValor,
                        'estado' => 'registrada',
                    ]
                );
                $guardadas++;
            }
            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'INSERT', 'notas',
                "Notas registradas por {$docenteNombre}: {$guardadas} postulantes en evaluación ID {$request->id_evaluacion}",
                $request->ip()
            );

            return redirect()->route('academico.notas.index', [
                'id_grupo' => $request->id_grupo,
                'id_evaluacion' => $request->id_evaluacion,
            ])->with('status', "Notas registradas para {$guardadas} postulantes.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar notas: ' . $e->getMessage()]);
        }
    }

    // Listar Postulantes con todos sus datos (para revisión antes de admisión)
    public function postulantesIndex(Request $request)
    {
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();
        $postulantes = collect();

        if ($gestionActiva) {
            $query = Postulante::with([
                'prepostulante',
                'usuario',
                'primeraOpcion',
                'segundaOpcion',
                'resultado',
                'admision',
                'notas.evaluacion',
            ])->where('id_gestion', $gestionActiva->id_gestion);

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->whereHas('prepostulante', function ($sq) use ($buscar) {
                        $sq->where('nombres', 'like', "%{$buscar}%")
                            ->orWhere('apellidos', 'like', "%{$buscar}%")
                            ->orWhere('ci', 'like', "%{$buscar}%");
                    });
                });
            }

            if ($request->filled('estado')) {
                $query->where('estado_postulante', $request->estado);
            }

            $postulantes = $query
                ->orderBy(
                    Prepostulante::selectRaw("apellidos")
                        ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')
                        ->limit(1)
                )
                ->orderBy(
                    Prepostulante::selectRaw("nombres")
                        ->whereColumn('prepostulantes.id_prepostulante', 'postulantes.id_prepostulante')
                        ->limit(1)
                )
                ->paginate(50);
        }

        return view('academico.postulantes', compact('gestionActiva', 'postulantes'));
    }

    // CU21 + CU22: Calcular Promedios y Verificar Estado
    public function promediosIndex(Request $request)
    {
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();
        $postulantes = collect();
        $materias = Materia::where('estado_activo', true)->orderBy('nombre_materia')->get();

        if ($gestionActiva) {
            $evaluaciones = Evaluacion::where('id_gestion', $gestionActiva->id_gestion)
                ->with('materia')
                ->orderBy('id_materia')
                ->orderBy('numero_evaluacion')
                ->get()
                ->groupBy('id_materia');

            $postulantesConNotas = Postulante::whereHas('notas', function ($q) use ($gestionActiva) {
                $q->whereHas('evaluacion', function ($q2) use ($gestionActiva) {
                    $q2->where('id_gestion', $gestionActiva->id_gestion);
                });
            })->with(['prepostulante', 'notas.evaluacion.materia'])->get();

            foreach ($postulantesConNotas as $p) {
                $notasPorMateria = $p->notas->groupBy(fn($n) => $n->evaluacion->id_materia);
                $promediosMateria = [];
                $materiasAprobadas = 0;
                $materiasCompletas = 0;

                foreach ($materias as $m) {
                    $evaluacionesMateria = $evaluaciones->get($m->id_materia, collect());
                    $notasMateria = $notasPorMateria->get($m->id_materia, collect());
                    $notasPorEvaluacion = $notasMateria->keyBy('id_evaluacion');

                    $sumaPonderada = 0;
                    $sumaPorcentajes = 0;
                    $completa = true;

                    foreach ($evaluacionesMateria as $ev) {
                        $notaReg = $notasPorEvaluacion->get($ev->id_evaluacion);
                        if ($notaReg) {
                            $sumaPonderada += $notaReg->nota * ($ev->porcentaje / 100);
                            $sumaPorcentajes += $ev->porcentaje;
                        } else {
                            $completa = false;
                        }
                    }

                    if ($completa && $sumaPorcentajes > 0) {
                        $promedio = round($sumaPonderada / ($sumaPorcentajes / 100), 2);
                        $promediosMateria[$m->id_materia] = [
                            'nombre' => $m->nombre_materia,
                            'promedio' => $promedio,
                            'aprobada' => $promedio >= 60,
                        ];
                        $materiasCompletas++;
                        if ($promedio >= 60) {
                            $materiasAprobadas++;
                        }
                    } else {
                        $promediosMateria[$m->id_materia] = [
                            'nombre' => $m->nombre_materia,
                            'promedio' => null,
                            'aprobada' => false,
                        ];
                    }
                }

                $promedioFinal = $materiasCompletas === $materias->count()
                    ? round(collect($promediosMateria)->avg('promedio'), 2)
                    : null;

                $estadoAcademico = null;
                if ($materiasCompletas === $materias->count()) {
                    $estadoAcademico = $materiasAprobadas === $materias->count() ? 'aprobado' : 'reprobado';
                }

                $resultadoExistente = $p->resultado;

                $postulantes->push([
                    'id' => $p->id_postulante,
                    'nombre' => $p->prepostulante?->nombres . ' ' . $p->prepostulante?->apellidos,
                    'ci' => $p->prepostulante?->ci,
                    'promedios_materia' => $promediosMateria,
                    'promedio_final' => $promedioFinal,
                    'estado_academico' => $estadoAcademico,
                    'materias_completas' => $materiasCompletas,
                    'materias_aprobadas' => $materiasAprobadas,
                    'total_materias' => $materias->count(),
                    'resultado_db' => $resultadoExistente,
                ]);
            }
        }

        $totalPostulantes = $postulantes->count();
        $completos = $postulantes->where('materias_completas', $materias->count());
        $aprobados = $completos->where('estado_academico', 'aprobado')->count();
        $reprobados = $completos->where('estado_academico', 'reprobado')->count();
        $incompletos = $postulantes->count() - $completos->count();

        $yaCalculados = Resultado::whereIn('id_postulante', $postulantes->pluck('id'))->count();

        return view('academico.promedios', compact(
            'postulantes', 'materias', 'gestionActiva',
            'totalPostulantes', 'completos', 'aprobados', 'reprobados', 'incompletos', 'yaCalculados'
        ));
    }

    public function promediosCalcular(Request $request)
    {
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();

        if (!$gestionActiva) {
            return back()->withErrors(['error' => 'No hay una gestión académica activa.']);
        }

        $evaluaciones = Evaluacion::where('id_gestion', $gestionActiva->id_gestion)
            ->with('materia')
            ->orderBy('id_materia')
            ->orderBy('numero_evaluacion')
            ->get()
            ->groupBy('id_materia');

        $materias = Materia::where('estado_activo', true)->count();

        $postulantes = Postulante::whereHas('notas', function ($q) use ($gestionActiva) {
            $q->whereHas('evaluacion', function ($q2) use ($gestionActiva) {
                $q2->where('id_gestion', $gestionActiva->id_gestion);
            });
        })->with(['notas.evaluacion.materia'])->get();

        $calculados = 0;
        $errores = 0;

        DB::beginTransaction();
        try {
            foreach ($postulantes as $p) {
                $notasPorMateria = $p->notas->groupBy(fn($n) => $n->evaluacion->id_materia);
                $materiasCompletas = 0;
                $materiasAprobadas = 0;
                $promedios = [];

                foreach ($evaluaciones as $idMateria => $evalsMateria) {
                    $notasMateria = $notasPorMateria->get($idMateria, collect());
                    $notasPorEvaluacion = $notasMateria->keyBy('id_evaluacion');

                    $sumaPonderada = 0;
                    $sumaPorcentajes = 0;
                    $completa = true;

                    foreach ($evalsMateria as $ev) {
                        $notaReg = $notasPorEvaluacion->get($ev->id_evaluacion);
                        if ($notaReg) {
                            $sumaPonderada += $notaReg->nota * ($ev->porcentaje / 100);
                            $sumaPorcentajes += $ev->porcentaje;
                        } else {
                            $completa = false;
                        }
                    }

                    if ($completa && $sumaPorcentajes > 0) {
                        $promedio = round($sumaPonderada / ($sumaPorcentajes / 100), 2);
                        $promedios[] = $promedio;
                        $materiasCompletas++;
                        if ($promedio >= 60) {
                            $materiasAprobadas++;
                        }
                    }
                }

                if ($materiasCompletas === $materias && !empty($promedios)) {
                    $promedioFinal = round(array_sum($promedios) / count($promedios), 2);
                    $estadoAcademico = $materiasAprobadas === $materias ? 'aprobado' : 'reprobado';

                    Resultado::updateOrCreate(
                        ['id_postulante' => $p->id_postulante],
                        [
                            'promedio_final' => $promedioFinal,
                            'estado_academico' => $estadoAcademico,
                            'publicado' => false,
                            'fecha_calculo' => now(),
                        ]
                    );
                    $calculados++;
                } else {
                    $errores++;
                }
            }

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'UPDATE', 'resultados',
                "Cálculo de promedios ejecutado: {$calculados} postulantes procesados, {$errores} con notas incompletas",
                $request->ip()
            );

            return redirect()->route('academico.promedios.index')
                ->with('status', "Cálculo completado. {$calculados} postulantes procesados" . ($errores ? " ({$errores} con notas incompletas omitidos)." : "."));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al calcular promedios: ' . $e->getMessage()]);
        }
    }

    // CU23: Ejecutar Admisión por Cupos
    public function admisionIndex(Request $request)
    {
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();

        if (!$gestionActiva) {
            return view('academico.admision', ['gestionActiva' => null]);
        }

        $carreras = Carrera::where('estado_activo', true)->orderBy('nombre_carrera')->get();
        $cupos = CupoCarrera::where('id_gestion', $gestionActiva->id_gestion)
            ->with('carrera')
            ->get()
            ->keyBy('id_carrera');

        $conResultado = Postulante::where('id_gestion', $gestionActiva->id_gestion)
            ->whereHas('resultado')
            ->with(['resultado', 'admision', 'prepostulante', 'primeraOpcion', 'segundaOpcion'])
            ->orderByDesc(
                Resultado::select('promedio_final')
                    ->whereColumn('resultados.id_postulante', 'postulantes.id_postulante')
                    ->limit(1)
            )->get();

        $aprobados = $conResultado->filter(fn($p) => $p->resultado->estado_academico === 'aprobado');
        $reprobados = $conResultado->filter(fn($p) => $p->resultado->estado_academico === 'reprobado');
        $admitidos = $conResultado->filter(fn($p) => $p->admision && $p->admision->estado_admision === 'admitido');
        $noAdmitidos = $conResultado->filter(fn($p) => $p->admision && $p->admision->estado_admision === 'no_admitido');
        $pendientes = $conResultado->filter(fn($p) => !$p->admision || $p->admision->estado_admision === 'pendiente');

        // Aprobadods sin admisión ejecutada
        $porAdmitir = $aprobados->filter(fn($p) => !$p->admision);

        return view('academico.admision', compact(
            'gestionActiva', 'carreras', 'cupos',
            'conResultado', 'aprobados', 'reprobados',
            'admitidos', 'noAdmitidos', 'pendientes', 'porAdmitir'
        ));
    }

    public function admisionCuposGuardar(Request $request)
    {
        $request->validate([
            'cupos' => ['required', 'array'],
            'cupos.*.id_cupo' => ['nullable', 'exists:cupos_carrera,id_cupo'],
            'cupos.*.id_carrera' => ['required', 'exists:carreras,id_carrera'],
            'cupos.*.cupos_totales' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $gestionActiva = GestionAdmision::activa()->first()
            ?? GestionAdmision::first();

        if (!$gestionActiva) {
            return back()->withErrors(['error' => 'No hay una gestión académica activa.']);
        }

        DB::beginTransaction();
        try {
            foreach ($request->cupos as $cupoData) {
                CupoCarrera::updateOrCreate(
                    [
                        'id_gestion' => $gestionActiva->id_gestion,
                        'id_carrera' => $cupoData['id_carrera'],
                    ],
                    [
                        'cupos_totales' => $cupoData['cupos_totales'],
                    ]
                );
            }

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'UPDATE', 'cupos_carrera',
                "Cupos actualizados para la gestión {$gestionActiva->nombre_gestion}",
                $request->ip()
            );

            return back()->with('status', 'Cupos actualizados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar cupos: ' . $e->getMessage()]);
        }
    }

    public function admisionEjecutar(Request $request)
    {
        $gestionActiva = GestionAdmision::activa()->first() ?? GestionAdmision::first();

        if (!$gestionActiva) {
            return back()->withErrors(['error' => 'No hay una gestión académica activa.']);
        }

        // Verificar que existan cupos configurados
        $totalCupos = CupoCarrera::where('id_gestion', $gestionActiva->id_gestion)->sum('cupos_totales');
        if ($totalCupos <= 0) {
            return back()->withErrors(['error' => 'No hay cupos configurados. Configurá los cupos por carrera primero.']);
        }

        // Obtener postulantes aprobados ordenados por promedio descendente
        $aprobados = Postulante::where('id_gestion', $gestionActiva->id_gestion)
            ->whereHas('resultado', function ($q) {
                $q->where('estado_academico', 'aprobado');
            })->with(['resultado', 'admision'])
            ->orderByDesc(
                Resultado::select('promedio_final')
                    ->whereColumn('resultados.id_postulante', 'postulantes.id_postulante')
                    ->limit(1)
            )->get();

        if ($aprobados->isEmpty()) {
            return back()->withErrors(['error' => 'No hay postulantes aprobados. Calculá los promedios primero.']);
        }

        // Resetear admisiones anteriores de esta gestión
        $idsPostulantes = $aprobados->pluck('id_postulante');
        Admision::whereIn('id_postulante', $idsPostulantes)->delete();

        // Resetear cupos_ocupados
        CupoCarrera::where('id_gestion', $gestionActiva->id_gestion)
            ->update(['cupos_ocupados' => 0]);

        DB::beginTransaction();
        try {
            $admitidos = 0;
            $noAdmitidos = 0;

            foreach ($aprobados as $p) {
                $estadoAdmision = 'no_admitido';
                $carreraAsignada = null;
                $opcionAsignada = null;
                $ordenMerito = 0;

                // Intentar 1ra opción
                $cupo1 = CupoCarrera::where('id_gestion', $gestionActiva->id_gestion)
                    ->where('id_carrera', $p->carrera_primera_opcion)
                    ->first();

                if ($cupo1 && $cupo1->cupos_disponibles > 0) {
                    $carreraAsignada = $p->carrera_primera_opcion;
                    $opcionAsignada = 1;
                    $estadoAdmision = 'admitido';
                    $cupo1->increment('cupos_ocupados');
                } else {
                    // Intentar 2da opción
                    $cupo2 = CupoCarrera::where('id_gestion', $gestionActiva->id_gestion)
                        ->where('id_carrera', $p->carrera_segunda_opcion)
                        ->first();

                    if ($cupo2 && $cupo2->cupos_disponibles > 0) {
                        $carreraAsignada = $p->carrera_segunda_opcion;
                        $opcionAsignada = 2;
                        $estadoAdmision = 'admitido';
                        $cupo2->increment('cupos_ocupados');
                    }
                }

                Admision::create([
                    'id_postulante' => $p->id_postulante,
                    'id_resultado' => $p->resultado?->id_resultado,
                    'id_carrera_asignada' => $carreraAsignada,
                    'opcion_asignada' => $opcionAsignada,
                    'orden_merito' => 0,
                    'estado_admision' => $estadoAdmision,
                ]);

                if ($estadoAdmision === 'admitido') {
                    $admitidos++;
                } else {
                    $noAdmitidos++;
                }
            }

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'INSERT', 'admisiones',
                "Admisión ejecutada: {$admitidos} admitidos, {$noAdmitidos} no admitidos de {$aprobados->count()} aprobados",
                $request->ip()
            );

            return redirect()->route('academico.admision.index')
                ->with('status', "Admisión ejecutada. {$admitidos} admitidos, {$noAdmitidos} no admitidos (de {$aprobados->count()} aprobados).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al ejecutar admisión: ' . $e->getMessage()]);
        }
    }
}

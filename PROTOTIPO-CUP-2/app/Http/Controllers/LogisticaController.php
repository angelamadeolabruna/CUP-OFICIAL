<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\CapacidadAula;
use App\Models\GestionAdmision;
use App\Models\Grupo;
use App\Models\GrupoHorario;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\PostulanteGrupo;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticaController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // CU26: Configurar Capacidad de Aula
    public function capacidadIndex()
    {
        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first();

        $capacidad = $gestion ? CapacidadAula::where('id_gestion', $gestion->id_gestion)->first() : null;

        return view('logistica.capacidad', compact('gestion', 'capacidad'));
    }

    public function capacidadStore(Request $request)
    {
        $request->validate([
            'max_estudiantes' => ['required', 'integer', 'min:1', 'max:500'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ], [
            'max_estudiantes.required' => 'La capacidad máxima es obligatoria.',
            'max_estudiantes.min' => 'La capacidad debe ser al menos 1.',
            'max_estudiantes.max' => 'La capacidad no puede superar 500.',
        ]);

        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first()
            ?? GestionAdmision::create([
                'nombre_gestion' => 'CUP FICCT ' . now()->year,
                'fecha_inicio' => now()->startOfYear(),
                'fecha_fin' => now()->endOfYear(),
                'estado' => 'activa',
            ]);

        CapacidadAula::updateOrCreate(
            ['id_gestion' => $gestion->id_gestion],
            [
                'max_estudiantes' => $request->max_estudiantes,
                'descripcion' => $request->descripcion,
            ]
        );

        $this->bitacoraService->registrar(
            Auth::id(),
            'UPDATE',
            'capacidades_aula',
            "Capacidad de aula configurada: {$request->max_estudiantes} estudiantes para gestión {$gestion->nombre_gestion}",
            $request->ip(),
            $gestion->id_gestion
        );

        return back()->with('status', 'Capacidad de aula configurada correctamente: ' . $request->max_estudiantes . ' estudiantes por grupo.');
    }

    // CU27: Calcular Cantidad de Grupos Necesarios
    public function gruposIndex()
    {
        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first();

        $capacidad = $gestion ? CapacidadAula::where('id_gestion', $gestion->id_gestion)->first() : null;

        $totalInscritos = $gestion
            ? Postulante::where('id_gestion', $gestion->id_gestion)
                ->whereIn('estado_postulante', ['inscrito', 'asignado'])
                ->count()
            : 0;

        $capacidadMaxima = $capacidad?->max_estudiantes;
        $gruposCalculados = null;
        $error = null;

        if ($totalInscritos === 0) {
            $error = 'No hay inscritos registrados en la gestión.';
        } elseif (!$capacidadMaxima) {
            $error = 'La capacidad de aula no está configurada. Configurá la capacidad primero.';
        } else {
            $gruposCalculados = (int) ceil($totalInscritos / $capacidadMaxima);
        }

        $totalMaterias = Materia::where('estado_activo', true)->count();

        return view('logistica.grupos', compact(
            'gestion',
            'capacidad',
            'totalInscritos',
            'capacidadMaxima',
            'gruposCalculados',
            'error',
            'totalMaterias'
        ));
    }

    // CU28: Asignar Grupos, Aulas, Horarios y Materias
    public function asignarIndex()
    {
        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first();

        $capacidad = $gestion ? CapacidadAula::where('id_gestion', $gestion->id_gestion)->first() : null;

        $totalInscritos = $gestion
            ? Postulante::where('id_gestion', $gestion->id_gestion)
                ->whereIn('estado_postulante', ['inscrito', 'asignado'])
                ->count()
            : 0;

        $gruposCalculados = ($totalInscritos > 0 && $capacidad?->max_estudiantes)
            ? (int) ceil($totalInscritos / $capacidad->max_estudiantes)
            : null;

        $grupos = $gestion
            ? Grupo::where('id_gestion', $gestion->id_gestion)
                ->with(['aula', 'materia', 'horarios.horario', 'postulantes.postulante.usuario'])
                ->orderBy('nombre_grupo')
                ->get()
            : collect();

        $aulas = Aula::where('estado_activo', true)->get();
        $materias = Materia::where('estado_activo', true)->get();
        $horarios = Horario::all();

        return view('logistica.asignar', compact(
            'gestion',
            'capacidad',
            'totalInscritos',
            'gruposCalculados',
            'grupos',
            'aulas',
            'materias',
            'horarios'
        ));
    }

    public function asignarGenerar(Request $request)
    {
        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first();

        if (!$gestion) {
            return back()->withErrors(['error' => 'No hay gestión activa.']);
        }

        $capacidad = CapacidadAula::where('id_gestion', $gestion->id_gestion)->first();
        if (!$capacidad) {
            return back()->withErrors(['error' => 'Configurá la capacidad de aula primero.']);
        }

        $totalInscritos = Postulante::where('id_gestion', $gestion->id_gestion)
            ->whereIn('estado_postulante', ['inscrito', 'asignado'])
            ->count();

        if ($totalInscritos === 0) {
            return back()->withErrors(['error' => 'No hay postulantes para distribuir.']);
        }

        $materias = Materia::where('estado_activo', true)->get();
        if ($materias->isEmpty()) {
            return back()->withErrors(['error' => 'No hay materias activas.']);
        }

        $cantidadPorMateria = (int) ceil($totalInscritos / $capacidad->max_estudiantes);

        // Eliminar grupos existentes para regenerar
        Grupo::where('id_gestion', $gestion->id_gestion)->delete();

        $totalCreados = 0;
        $nombres = [];

        foreach ($materias as $materia) {
            $abrev = match (mb_strtolower($materia->nombre_materia)) {
                'matemáticas' => 'MAT',
                'computación' => 'COMP',
                'inglés' => 'INGL',
                'física' => 'FISI',
                default => mb_strtoupper(mb_substr($materia->nombre_materia, 0, 4)),
            };
            for ($i = 1; $i <= $cantidadPorMateria; $i++) {
                Grupo::create([
                    'id_gestion' => $gestion->id_gestion,
                    'id_materia' => $materia->id_materia,
                    'nombre_grupo' => $abrev . 'S' . $i,
                    'capacidad_maxima' => $capacidad->max_estudiantes,
                    'estado' => 'activo',
                ]);
                $totalCreados++;
                $nombres[] = $abrev . 'S' . $i;
            }
        }

        $this->bitacoraService->registrar(
            Auth::id(),
            'INSERT',
            'grupos',
            "{$totalCreados} grupos generados ({$cantidadPorMateria} por materia) para gestión {$gestion->nombre_gestion}",
            $request->ip(),
            $gestion->id_gestion
        );

        return redirect()->route('logistica.asignar.index')
            ->with('status', "{$totalCreados} grupos generados ({$cantidadPorMateria} por materia) correctamente.");
    }

    public function asignarActualizarAula(Request $request, $idGrupo)
    {
        $grupo = Grupo::findOrFail($idGrupo);

        $request->validate([
            'id_aula' => ['nullable', 'exists:aulas,id_aula'],
        ]);

        $grupo->update(['id_aula' => $request->id_aula]);

        $this->bitacoraService->registrar(
            Auth::id(),
            'UPDATE',
            'grupos',
            "Aula asignada al grupo {$grupo->nombre_grupo}",
            $request->ip(),
            $grupo->id_grupo
        );

        return back()->with('status', "Aula asignada al grupo {$grupo->nombre_grupo}.");
    }

    public function asignarAgregarHorario(Request $request)
    {
        $request->validate([
            'id_grupo' => ['required', 'exists:grupos,id_grupo'],
            'id_horarios' => ['required', 'array', 'min:1'],
            'id_horarios.*' => ['required', 'exists:horarios,id_horario'],
        ], [
            'id_horarios.required' => 'Seleccioná al menos un horario.',
        ]);

        $asignados = 0;
        foreach ($request->id_horarios as $idHorario) {
            try {
                GrupoHorario::create([
                    'id_grupo' => $request->id_grupo,
                    'id_horario' => $idHorario,
                ]);
                $asignados++;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Saltar si ya existe
            }
        }

        if ($asignados === 0) {
            return back()->withErrors(['error' => 'Los horarios seleccionados ya estaban asignados a este grupo.']);
        }

        $this->bitacoraService->registrar(
            Auth::id(),
            'INSERT',
            'grupo_horarios',
            "{$asignados} horarios asignados al grupo",
            $request->ip(),
            $request->id_grupo
        );

        return back()->with('status', "{$asignados} horarios asignados correctamente.");
    }

    public function asignarQuitarHorario(Request $request, $idGrupoHorario)
    {
        $gh = GrupoHorario::findOrFail($idGrupoHorario);
        $gh->delete();

        $this->bitacoraService->registrar(
            Auth::id(),
            'DELETE',
            'grupo_horarios',
            'Horario quitado del grupo',
            $request->ip(),
            $idGrupoHorario
        );

        return back()->with('status', 'Horario quitado del grupo.');
    }

    public function asignarDistribuir(Request $request)
    {
        $gestion = GestionAdmision::where('estado', 'activa')->first()
            ?? GestionAdmision::first();

        if (!$gestion) {
            return back()->withErrors(['error' => 'No hay gestión activa.']);
        }

        $materias = Materia::where('estado_activo', true)->get();
        if ($materias->isEmpty()) {
            return back()->withErrors(['error' => 'No hay materias activas.']);
        }

        $postulantes = Postulante::where('id_gestion', $gestion->id_gestion)
            ->whereIn('estado_postulante', ['inscrito', 'asignado'])
            ->orderBy('id_postulante')
            ->get();

        if ($postulantes->isEmpty()) {
            return back()->withErrors(['error' => 'No hay postulantes para distribuir.']);
        }

        // Limpiar distribuciones previas
        $todosGrupos = Grupo::where('id_gestion', $gestion->id_gestion)->pluck('id_grupo');
        PostulanteGrupo::whereIn('id_grupo', $todosGrupos)->delete();

        $asignados = 0;
        DB::beginTransaction();
        try {
            // Distribuir por cada materia
            foreach ($materias as $materia) {
                $gruposMateria = Grupo::where('id_gestion', $gestion->id_gestion)
                    ->where('id_materia', $materia->id_materia)
                    ->orderBy('nombre_grupo')
                    ->get();

                if ($gruposMateria->isEmpty()) continue;

                $cantGrupos = $gruposMateria->count();
                foreach ($postulantes as $i => $postulante) {
                    $idxGrupo = $i % $cantGrupos;
                    PostulanteGrupo::create([
                        'id_postulante' => $postulante->id_postulante,
                        'id_grupo' => $gruposMateria[$idxGrupo]->id_grupo,
                        'estado' => 'activo',
                    ]);
                    $asignados++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al distribuir postulantes: ' . $e->getMessage()]);
        }

        // Actualizar estado_postulante a 'asignado'
        Postulante::whereIn('id_postulante', $postulantes->pluck('id_postulante'))
            ->update(['estado_postulante' => 'asignado']);

        $this->bitacoraService->registrar(
            Auth::id(),
            'UPDATE',
            'postulante_grupos',
            "{$asignados} asignaciones creadas: postulantes distribuidos en " . $materias->count() . " materias",
            $request->ip(),
            $gestion->id_gestion
        );

        return back()->with('status', "{$asignados} asignaciones creadas: postulantes distribuidos en " . $materias->count() . " materias.");
    }

    // CU29: Registrar Aulas
    public function aulasIndex()
    {
        $aulas = Aula::orderBy('codigo_aula')->get();
        return view('logistica.aulas', compact('aulas'));
    }

    public function aulasStore(Request $request)
    {
        $request->validate([
            'codigo_aula' => ['required', 'string', 'max:30', 'unique:aulas,codigo_aula'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:500'],
        ], [
            'codigo_aula.required' => 'El código de aula es obligatorio.',
            'codigo_aula.unique' => 'Ese código de aula ya existe.',
            'capacidad.min' => 'La capacidad debe ser al menos 1.',
        ]);

        Aula::create($request->only(['codigo_aula', 'ubicacion', 'capacidad']));

        $this->bitacoraService->registrar(
            Auth::id(), 'INSERT', 'aulas',
            "Aula creada: {$request->codigo_aula}",
            $request->ip()
        );

        return redirect()->route('logistica.aulas.index')
            ->with('status', "Aula {$request->codigo_aula} registrada correctamente.");
    }

    public function aulasUpdate(Request $request, $idAula)
    {
        $aula = Aula::findOrFail($idAula);

        $request->validate([
            'codigo_aula' => ['required', 'string', 'max:30', 'unique:aulas,codigo_aula,' . $idAula . ',id_aula'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:500'],
            'estado_activo' => ['boolean'],
        ]);

        $aula->update($request->only(['codigo_aula', 'ubicacion', 'capacidad', 'estado_activo']));

        $this->bitacoraService->registrar(
            Auth::id(), 'UPDATE', 'aulas',
            "Aula actualizada: {$request->codigo_aula}",
            $request->ip(),
            $idAula
        );

        return redirect()->route('logistica.aulas.index')
            ->with('status', "Aula {$request->codigo_aula} actualizada.");
    }

    public function aulasDestroy(Request $request, $idAula)
    {
        $aula = Aula::findOrFail($idAula);
        $codigo = $aula->codigo_aula;
        $aula->delete();

        $this->bitacoraService->registrar(
            Auth::id(), 'DELETE', 'aulas',
            "Aula eliminada: {$codigo}",
            $request->ip(),
            $idAula
        );

        return redirect()->route('logistica.aulas.index')
            ->with('status', "Aula {$codigo} eliminada.");
    }

    // Horarios: Registrar horarios
    public function horariosIndex()
    {
        $horarios = Horario::orderByRaw("CASE dia_semana WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miércoles' THEN 3 WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 WHEN 'Sábado' THEN 6 ELSE 7 END")
            ->orderBy('hora_inicio')
            ->get();
        return view('logistica.horarios', compact('horarios'));
    }

    public function horariosStore(Request $request)
    {
        $request->validate([
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['required', 'string', 'max:20'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'turno' => ['nullable', 'string', 'max:30'],
        ], [
            'dias.required' => 'Seleccioná al menos un día.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $creados = 0;
        $diasNombres = [];
        foreach ($request->dias as $dia) {
            Horario::create([
                'dia_semana' => $dia,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'turno' => $request->turno,
            ]);
            $creados++;
            $diasNombres[] = $dia;
        }

        $this->bitacoraService->registrar(
            Auth::id(), 'INSERT', 'horarios',
            "{$creados} horarios creados: " . implode(', ', $diasNombres) . " {$request->hora_inicio}-{$request->hora_fin}",
            $request->ip()
        );

        return redirect()->route('logistica.horarios.index')
            ->with('status', "{$creados} horarios registrados ({$request->hora_inicio}-{$request->hora_fin}, " . implode(', ', $diasNombres) . ').');
    }

    public function horariosUpdate(Request $request, $idHorario)
    {
        $horario = Horario::findOrFail($idHorario);

        $request->validate([
            'dia_semana' => ['required', 'string', 'max:20'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'turno' => ['nullable', 'string', 'max:30'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $horario->update($request->only(['dia_semana', 'hora_inicio', 'hora_fin', 'turno']));

        $this->bitacoraService->registrar(
            Auth::id(), 'UPDATE', 'horarios',
            "Horario actualizado: {$request->dia_semana} {$request->hora_inicio}-{$request->hora_fin}",
            $request->ip(),
            $idHorario
        );

        return redirect()->route('logistica.horarios.index')
            ->with('status', 'Horario actualizado correctamente.');
    }

    public function horariosDestroy(Request $request, $idHorario)
    {
        $horario = Horario::findOrFail($idHorario);
        $horario->delete();

        $this->bitacoraService->registrar(
            Auth::id(), 'DELETE', 'horarios',
            "Horario eliminado",
            $request->ip(),
            $idHorario
        );

        return redirect()->route('logistica.horarios.index')
            ->with('status', 'Horario eliminado.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\CargaHoraria;
use App\Models\Docente;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\PostulanteGrupo;
use App\Models\RequisitoDocente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\EmailService;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DocenteController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // CU30: Registrar y Validar Docente
    public function index()
    {
        $docentes = Docente::with(['usuario', 'requisitos'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('docentes.index', compact('docentes'));
    }

    public function create()
    {
        return view('docentes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ci' => ['required', 'string', 'max:30', 'unique:docentes,ci'],
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'profesion' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:120', 'unique:docentes,correo'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'crear_usuario' => ['boolean'],
            'nombre_usuario' => ['required_if:crear_usuario,1', 'nullable', 'string', 'max:80', 'unique:usuarios,nombre_usuario'],
            'password' => ['required_if:crear_usuario,1', 'nullable', 'string', 'min:6'],
        ], [
            'ci.unique' => 'Este CI ya está registrado como docente.',
            'correo.unique' => 'Este correo ya está registrado como docente.',
            'nombre_usuario.unique' => 'Ese nombre de usuario ya existe.',
            'crear_usuario.boolean' => 'El campo crear usuario debe ser verdadero o falso.',
        ]);

        DB::beginTransaction();
        try {
            $idUsuario = null;

            if ($request->boolean('crear_usuario')) {
                $rolDocente = Rol::where('nombre_rol', 'docente')->firstOrFail();

                $usuario = Usuario::create([
                    'id_rol' => $rolDocente->id_rol,
                    'email' => $request->correo,
                    'ci' => $request->ci,
                    'nombre_usuario' => $request->nombre_usuario,
                    'password_hash' => Hash::make($request->password),
                    'estado' => 'activo',
                ]);
                $idUsuario = $usuario->id_usuario;

                EmailService::enviarCredenciales($usuario, $request->password);
            }

            $docente = Docente::create([
                'id_usuario' => $idUsuario,
                'ci' => $request->ci,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'profesion' => $request->profesion,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'estado_docente' => 'pendiente',
            ]);

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'INSERT', 'docentes',
                "Docente registrado: {$docente->nombres} {$docente->apellidos} (CI: {$docente->ci})",
                $request->ip(),
                $docente->id_docente
            );

            return redirect()->route('docentes.show', $docente->id_docente)
                ->with('status', 'Docente registrado correctamente. Adjuntá los requisitos académicos.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar docente: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $docente = Docente::with(['usuario', 'requisitos'])->findOrFail($id);

        return view('docentes.show', compact('docente'));
    }

    public function subirRequisito(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);

        $request->validate([
            'tipo_requisito' => ['required', 'string', 'max:80'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'archivo.max' => 'El archivo no debe superar 5MB.',
            'archivo.mimes' => 'Solo se aceptan PDF, JPG, PNG.',
        ]);

        $ruta = $request->file('archivo')->store('requisitos_docente', 'public');

        RequisitoDocente::create([
            'id_docente' => $docente->id_docente,
            'tipo_requisito' => $request->tipo_requisito,
            'archivo_url' => $ruta,
            'estado_revision' => 'pendiente',
        ]);

        $this->bitacoraService->registrar(
            Auth::id(), 'INSERT', 'requisitos_docente',
            "Requisito subido: {$request->tipo_requisito} para docente {$docente->nombres} {$docente->apellidos}",
            $request->ip(),
            $docente->id_docente
        );

        return back()->with('status', 'Requisito subido correctamente. Pendiente de revisión.');
    }

    public function revisarRequisito(Request $request, $idRequisito)
    {
        $requisito = RequisitoDocente::with('docente')->findOrFail($idRequisito);

        $request->validate([
            'estado_revision' => ['required', 'in:aprobado,rechazado'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $requisito->update([
            'estado_revision' => $request->estado_revision,
            'observacion' => $request->observacion,
        ]);

        $this->bitacoraService->registrar(
            Auth::id(), 'UPDATE', 'requisitos_docente',
            "Requisito {$request->estado_revision}: {$requisito->tipo_requisito}",
            $request->ip(),
            $idRequisito
        );

        return back()->with('status', 'Requisito ' . ($request->estado_revision === 'aprobado' ? 'aprobado' : 'rechazado') . '.');
    }

    public function aprobar(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $docente->update(['estado_docente' => 'aprobado']);

        $this->bitacoraService->registrar(
            Auth::id(), 'UPDATE', 'docentes',
            "Docente aprobado: {$docente->nombres} {$docente->apellidos}",
            $request->ip(),
            $docente->id_docente
        );

        return back()->with('status', "Docente {$docente->nombre_completo} aprobado.");
    }

    public function rechazar(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $docente->update(['estado_docente' => 'rechazado']);

        $this->bitacoraService->registrar(
            Auth::id(), 'UPDATE', 'docentes',
            "Docente rechazado: {$docente->nombres} {$docente->apellidos}",
            $request->ip(),
            $docente->id_docente
        );

        return back()->with('status', "Docente {$docente->nombre_completo} rechazado.");
    }

    public function crearUsuario(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);

        if ($docente->id_usuario) {
            return back()->withErrors(['error' => 'Este docente ya tiene un usuario asociado.']);
        }

        $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:80', 'unique:usuarios,nombre_usuario'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya existe.',
        ]);

        $rolDocente = Rol::where('nombre_rol', 'docente')->firstOrFail();

        DB::beginTransaction();
        try {
            $usuario = Usuario::create([
                'id_rol' => $rolDocente->id_rol,
                'email' => $docente->correo,
                'ci' => $docente->ci,
                'nombre_usuario' => $request->nombre_usuario,
                'password_hash' => Hash::make($request->password),
                'estado' => 'activo',
            ]);

            $docente->update(['id_usuario' => $usuario->id_usuario]);

            DB::commit();

            EmailService::enviarCredenciales($usuario, $request->password);

            $this->bitacoraService->registrar(
                Auth::id(), 'UPDATE', 'docentes',
                "Usuario creado para docente: {$docente->nombre_completo}",
                $request->ip(),
                $docente->id_docente
            );

            return back()->with('status', "Usuario {$request->nombre_usuario} creado para {$docente->nombre_completo}. Se enviaron las credenciales a {$docente->correo}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear usuario: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $nombre = $docente->nombre_completo;
        $idUsuario = $docente->id_usuario;

        DB::beginTransaction();
        try {
            $docente->delete();

            if ($idUsuario && $request->boolean('eliminar_usuario')) {
                Usuario::where('id_usuario', $idUsuario)->delete();
            }

            DB::commit();

            $this->bitacoraService->registrar(
                Auth::id(), 'DELETE', 'docentes',
                "Docente eliminado: {$nombre}" . ($eliminarUsuario ? ' (usuario incluido)' : ''),
                $request->ip(),
                $id
            );

            return redirect()->route('docentes.index')
                ->with('status', "Docente {$nombre} eliminado" . ($eliminarUsuario ? ' junto con su usuario.' : '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar docente: ' . $e->getMessage()]);
        }
    }

    // CU31: Asignar Carga Horaria y Grupos a Docentes
    public function cargaHorariaIndex()
    {
        $docentes = Docente::with(['usuario', 'cargasHorarias.grupo.aula', 'cargasHorarias.grupo.materia', 'cargasHorarias.grupo.horarios.horario'])
            ->where('estado_docente', 'aprobado')
            ->orderBy('apellidos')
            ->get();

        $grupos = Grupo::where('estado', 'activo')
            ->with(['aula', 'materia', 'horarios.horario'])
            ->orderBy('nombre_grupo')
            ->get();

        return view('docentes.carga-horaria', compact('docentes', 'grupos'));
    }

    public function cargaHorariaStore(Request $request)
    {
        $request->validate([
            'id_docente' => ['required', 'exists:docentes,id_docente'],
            'id_grupo' => ['required', 'exists:grupos,id_grupo'],
        ]);

        $docente = Docente::findOrFail($request->id_docente);

        if ($docente->estado_docente !== 'aprobado') {
            return back()->withErrors(['error' => 'El docente debe estar aprobado para asignarle carga horaria.']);
        }

        // Validar máximo 4 grupos distintos
        $gruposActuales = CargaHoraria::where('id_docente', $docente->id_docente)
            ->distinct('id_grupo')
            ->count('id_grupo');

        $yaAsignadoEsteGrupo = CargaHoraria::where('id_docente', $docente->id_docente)
            ->where('id_grupo', $request->id_grupo)
            ->exists();

        if (!$yaAsignadoEsteGrupo && $gruposActuales >= 4) {
            return back()->withErrors(['error' => 'El docente ya tiene asignados 4 grupos. No puede superar ese límite.']);
        }

        // Validar choque de horario entre grupos del mismo docente
        $grupoNuevo = Grupo::with('horarios.horario')->find($request->id_grupo);
        if ($grupoNuevo && $grupoNuevo->horarios->isNotEmpty()) {
            $idsDiasNuevo = $grupoNuevo->horarios->map(fn($gh) => $gh->horario->dia_semana . '|' . $gh->horario->hora_inicio . '|' . $gh->horario->hora_fin);

            $gruposActualesCH = CargaHoraria::where('id_docente', $docente->id_docente)
                ->with('grupo.horarios.horario')
                ->get();

            foreach ($gruposActualesCH as $ch) {
                foreach ($ch->grupo->horarios as $gh) {
                    $h = $gh->horario;
                    $claveActual = $h->dia_semana . '|' . $h->hora_inicio . '|' . $h->hora_fin;
                    if ($idsDiasNuevo->contains($claveActual)) {
                        return back()->withErrors(['error' => "El docente ya tiene un grupo ({$ch->grupo->nombre_grupo}) en el mismo día y horario."]);
                    }
                }
            }
        }

        try {
            CargaHoraria::create([
                'id_docente' => $request->id_docente,
                'id_grupo' => $request->id_grupo,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return back()->withErrors(['error' => 'Esa asignación ya existe.']);
        }

        $this->bitacoraService->registrar(
            Auth::id(), 'INSERT', 'cargas_horarias',
            "Carga horaria asignada a docente {$docente->nombre_completo}",
            $request->ip(),
            $request->id_docente
        );

        return back()->with('status', 'Carga horaria asignada correctamente.');
    }

    public function cargaHorariaQuitar(Request $request, $idCarga)
    {
        $carga = CargaHoraria::findOrFail($idCarga);
        $docente = $carga->docente;
        $grupo = $carga->grupo;
        $carga->delete();

        $this->bitacoraService->registrar(
            Auth::id(), 'DELETE', 'cargas_horarias',
            "Carga horaria quitada a {$docente?->nombre_completo} para grupo {$grupo?->nombre_grupo}",
            $request->ip(),
            $idCarga
        );

        return back()->with('status', 'Carga horaria eliminada.');
    }

    // CU32: Consultar Carga Horaria (vista del docente)
    public function miCargaHoraria()
    {
        $docente = Docente::where('id_usuario', Auth::id())->first()
            ?? Docente::where('correo', Auth::user()->email)->first();

        if (!$docente) {
            return view('docentes.mi-carga-horaria', ['sinRegistro' => true]);
        }

        // Si el docente no tenía id_usuario, lo vinculamos ahora
        if (!$docente->id_usuario) {
            $docente->update(['id_usuario' => Auth::id()]);
        }

        $docente->load(['cargasHorarias.grupo.aula', 'cargasHorarias.grupo.materia', 'cargasHorarias.grupo.horarios.horario', 'cargasHorarias.grupo.postulantes']);

        return view('docentes.mi-carga-horaria', compact('docente'));
    }

    // CU33: Registrar Asistencia
    public function asistenciaIndex(Request $request)
    {
        $docente = Docente::where('id_usuario', Auth::id())->first()
            ?? Docente::where('correo', Auth::user()->email)->first();

        if (!$docente) {
            return view('docentes.asistencia', ['sinRegistro' => true]);
        }

        if (!$docente->id_usuario) {
            $docente->update(['id_usuario' => Auth::id()]);
        }

        $docente->load('cargasHorarias.grupo.materia');
        $grupos = $docente->cargasHorarias->pluck('grupo')->filter()->unique('id_grupo')->values();

        $grupoSeleccionado = null;
        $postulantes = collect();
        $fecha = $request->input('fecha', now()->toDateString());
        $idGrupo = $request->input('id_grupo');
        $asistenciasExistentes = collect();

        if ($idGrupo && $grupos->firstWhere('id_grupo', $idGrupo)) {
            $grupoSeleccionado = $grupos->firstWhere('id_grupo', $idGrupo);

            $idsPostulantes = PostulanteGrupo::where('id_grupo', $idGrupo)->pluck('id_postulante');

            $postulantes = Postulante::whereIn('id_postulante', $idsPostulantes)
                ->with('usuario')
                ->orderBy('id_postulante')
                ->get();

            $asistenciasExistentes = Asistencia::where('id_grupo', $idGrupo)
                ->where('fecha_clase', $fecha)
                ->get()
                ->keyBy('id_postulante');
        }

        return view('docentes.asistencia', compact(
            'docente', 'grupos', 'grupoSeleccionado',
            'postulantes', 'fecha', 'asistenciasExistentes'
        ));
    }

    public function asistenciaStore(Request $request)
    {
        $docente = Docente::where('id_usuario', Auth::id())->first()
            ?? Docente::where('correo', Auth::user()->email)->first();

        if (!$docente) {
            return back()->withErrors(['error' => 'No estás registrado como docente.']);
        }

        $request->validate([
            'id_grupo' => ['required', 'exists:grupos,id_grupo'],
            'fecha_clase' => ['required', 'date'],
            'asistencia' => ['required', 'array'],
            'asistencia.*.estado' => ['required', 'in:presente,ausente,justificado'],
            'asistencia.*.observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $asistencias = $request->input('asistencia');
        $guardadas = 0;

        DB::beginTransaction();
        try {
            foreach ($asistencias as $idPostulante => $data) {
                Asistencia::updateOrCreate(
                    [
                        'id_postulante' => $idPostulante,
                        'id_grupo' => $request->id_grupo,
                        'fecha_clase' => $request->fecha_clase,
                    ],
                    [
                        'id_docente' => $docente->id_docente,
                        'estado_asistencia' => $data['estado'],
                        'observacion' => $data['observacion'] ?? null,
                    ]
                );
                $guardadas++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar asistencia: ' . $e->getMessage()]);
        }

        return redirect()->route('docentes.asistencia.index', [
            'id_grupo' => $request->id_grupo,
            'fecha' => $request->fecha_clase,
        ])->with('status', "Asistencia registrada para {$guardadas} postulantes.");
    }

    // CU34: Consultar Asistencia
    public function consultarAsistencia(Request $request)
    {
        $rol = Auth::user()->rol?->nombre_rol;
        $query = Asistencia::with(['postulante.usuario', 'grupo.materia', 'docente']);

        // Filtros según el rol
        if ($rol === 'docente') {
            $docente = Docente::where('id_usuario', Auth::id())->first()
                ?? Docente::where('correo', Auth::user()->email)->first();

            if (!$docente) {
                return view('docentes.consultar-asistencia', ['sinRegistro' => true]);
            }

            $query->where('id_docente', $docente->id_docente);
        } elseif ($rol === 'postulante_oficial') {
            $postulante = Postulante::where('id_usuario', Auth::id())->first();

            if (!$postulante) {
                return view('docentes.consultar-asistencia', ['sinRegistro' => true]);
            }

            $query->where('id_postulante', $postulante->id_postulante);
        }

        // Filtros adicionales (comunes)
        if ($request->filled('id_grupo')) {
            $query->where('id_grupo', $request->id_grupo);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_clase', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_clase', '<=', $request->fecha_hasta);
        }
        if ($request->filled('estado_asistencia')) {
            $query->where('estado_asistencia', $request->estado_asistencia);
        }

        $asistencias = $query->orderByDesc('fecha_clase')
            ->orderBy('id_grupo')
            ->orderBy('id_postulante')
            ->paginate(50);

        // Datos para filtros
        $grupos = collect();
        if (in_array($rol, ['administrador', 'coordinador_academico'])) {
            $grupos = Grupo::with('materia')->where('estado', 'activo')->orderBy('nombre_grupo')->get();
        } elseif ($rol === 'docente' && isset($docente)) {
            $docente->load('cargasHorarias.grupo.materia');
            $grupos = $docente->cargasHorarias->pluck('grupo')->filter()->unique('id_grupo')->values();
        }

        return view('docentes.consultar-asistencia', compact('asistencias', 'grupos', 'rol'));
    }
}

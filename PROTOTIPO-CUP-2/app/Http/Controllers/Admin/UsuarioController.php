<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaUsuario;
use App\Models\Carrera;
use App\Models\DatosRegistroTemporal;
use App\Models\GestionAdmision;
use App\Models\Postulante;
use App\Models\Prepostulante;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // CU05 F2: listar usuarios registrados
    public function index(Request $request)
    {
        $query = Usuario::with('rol')->orderByDesc('created_at');

        if ($busqueda = $request->input('busqueda')) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('lower(nombre_usuario) like ?', ['%' . strtolower($busqueda) . '%'])
                  ->orWhereRaw('lower(email) like ?', ['%' . strtolower($busqueda) . '%'])
                  ->orWhere('ci', 'like', "%{$busqueda}%")
                  ->orWhereHas('rol', fn($r) => $r->whereRaw('lower(nombre_rol) like ?', ['%' . strtolower($busqueda) . '%']));
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($idRol = $request->input('id_rol')) {
            $query->where('id_rol', $idRol);
        }

        $usuarios = $query->paginate(20)->withQueryString();
        $roles    = Rol::where('estado_activo', true)->orderBy('nombre_rol')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    // CU05 F3 (crear): mostrar formulario
    public function create()
    {
        $roles = Rol::where('estado_activo', true)->orderBy('nombre_rol')->get();
        $carreras = Carrera::where('estado_activo', true)->orderBy('codigo_carrera')->get();
        return view('admin.usuarios.crear', compact('roles', 'carreras'));
    }

    // CU05 F3/F4/F5/F6/F7: guardar nuevo usuario y enviar credenciales
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre_usuario'          => ['required', 'string', 'max:120'],
            'email'                   => ['required', 'email', 'max:120', 'unique:usuarios,email'],
            'ci'                      => ['nullable', 'string', 'max:20', 'unique:usuarios,ci'],
            'id_rol'                  => ['required', 'exists:roles,id_rol'],
            'password'                => ['required', 'string', 'min:8', 'confirmed'],
            'estado'                  => ['required', Rule::in(['activo', 'inactivo'])],
            'nombres'                 => ['nullable', 'string', 'max:120'],
            'apellidos'               => ['nullable', 'string', 'max:120'],
            'carrera_primera_opcion'  => ['nullable', 'integer', 'exists:carreras,id_carrera'],
            'carrera_segunda_opcion'  => ['nullable', 'integer', 'exists:carreras,id_carrera', 'different:carrera_primera_opcion'],
            'fecha_nacimiento'        => ['nullable', 'date', 'before:' . now()->subYears(14)->format('Y-m-d')],
            'sexo'                    => ['nullable', 'string', 'max:20'],
            'direccion'               => ['nullable', 'string', 'max:500'],
            'telefono'                => ['nullable', 'string', 'max:30'],
            'colegio_procedencia'     => ['nullable', 'string', 'max:160'],
            'ciudad'                  => ['nullable', 'string', 'max:80'],
            'titulo_bachiller'        => ['nullable', 'boolean'],
        ], [
            'email.unique'    => 'El correo ya está registrado. (E1: Usuario duplicado)',
            'ci.unique'       => 'El CI ya está registrado. (E1: Usuario duplicado)',
            'id_rol.exists'   => 'El rol seleccionado no es válido. (E2: Rol inválido)',
        ]);

        $passwordPlano = $datos['password'];

        $usuario = DB::transaction(function () use ($datos, $passwordPlano, $request) {
            $usuario = Usuario::create([
                'nombre_usuario' => $datos['nombre_usuario'],
                'email'          => strtolower(trim($datos['email'])),
                'ci'             => $datos['ci'] ?? null,
                'id_rol'         => $datos['id_rol'],
                'password_hash'  => Hash::make($passwordPlano),
                'estado'         => $datos['estado'],
            ]);

            $usuario->load('rol');

            $this->bitacoraService->registrar(
                Auth::id(),
                'CREATE',
                'usuarios',
                "Cuenta creada para {$usuario->email} con rol ID {$usuario->id_rol}",
                $request->ip(),
                $usuario->id_usuario
            );

            // Crear registro en postulantes si el rol es postulante_oficial
            if ($usuario->rol?->nombre_rol === 'postulante_oficial') {
                $gestion = GestionAdmision::where('estado', 'activa')->first()
                    ?? GestionAdmision::first()
                    ?? GestionAdmision::create([
                        'nombre_gestion' => 'CUP FICCT ' . now()->year,
                        'fecha_inicio' => now()->startOfYear(),
                        'fecha_fin' => now()->endOfYear(),
                        'estado' => 'activa',
                    ]);

                $prepostulante = Prepostulante::firstOrCreate(
                    ['correo' => $usuario->email],
                    [
                        'id_gestion' => $gestion->id_gestion,
                        'ci' => $usuario->ci ?? 'SIN_CI',
                        'nombres' => $datos['nombres'] ?? $usuario->nombre_usuario,
                        'apellidos' => $datos['apellidos'] ?? '',
                        'telefono' => $datos['telefono'] ?? null,
                        'estado_proceso' => 'registro_completo',
                    ]
                );

                Postulante::firstOrCreate(
                    ['id_prepostulante' => $prepostulante->id_prepostulante],
                    [
                    'id_prepostulante' => $prepostulante->id_prepostulante,
                    'id_usuario' => $usuario->id_usuario,
                    'id_gestion' => $gestion->id_gestion,
                    'carrera_primera_opcion' => $datos['carrera_primera_opcion'] ?? null,
                    'carrera_segunda_opcion' => $datos['carrera_segunda_opcion'] ?? null,
                    'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                    'sexo' => $datos['sexo'] ?? null,
                    'direccion' => $datos['direccion'] ?? '',
                    'telefono' => $datos['telefono'] ?? '',
                    'correo' => $usuario->email,
                    'colegio_procedencia' => $datos['colegio_procedencia'] ?? '',
                    'ciudad' => $datos['ciudad'] ?? '',
                    'titulo_bachiller' => $datos['titulo_bachiller'] ?? false,
                    'estado_postulante' => 'inscrito',
                ]);
            }

            // Crear registro en prepostulantes si el rol es prepostulante
            if ($usuario->rol?->nombre_rol === 'prepostulante') {
                $gestion = GestionAdmision::where('estado', 'activa')->first()
                    ?? GestionAdmision::first()
                    ?? GestionAdmision::create([
                        'nombre_gestion' => 'CUP FICCT ' . now()->year,
                        'fecha_inicio' => now()->startOfYear(),
                        'fecha_fin' => now()->endOfYear(),
                        'estado' => 'activa',
                    ]);

                $prepostulante = Prepostulante::firstOrCreate(
                    ['correo' => $usuario->email],
                    [
                        'id_gestion' => $gestion->id_gestion,
                        'ci' => $usuario->ci ?? 'SIN_CI',
                        'nombres' => $datos['nombres'] ?? $usuario->nombre_usuario,
                        'apellidos' => $datos['apellidos'] ?? '',
                        'telefono' => $datos['telefono'] ?? null,
                        'estado_proceso' => $datos['carrera_primera_opcion'] ? 'registro_completo' : 'prepostulado',
                    ]
                );

                if ($datos['carrera_primera_opcion'] ?? null) {
                    DatosRegistroTemporal::create([
                        'id_prepostulante' => $prepostulante->id_prepostulante,
                        'carrera_primera_opcion' => $datos['carrera_primera_opcion'],
                        'carrera_segunda_opcion' => $datos['carrera_segunda_opcion'] ?? null,
                        'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? now()->subYears(18),
                        'sexo' => $datos['sexo'] ?? 'No especificado',
                        'direccion' => $datos['direccion'] ?? '',
                        'telefono' => $datos['telefono'] ?? '',
                        'correo' => $usuario->email,
                        'colegio_procedencia' => $datos['colegio_procedencia'] ?? '',
                        'ciudad' => $datos['ciudad'] ?? '',
                        'titulo_bachiller' => $datos['titulo_bachiller'] ?? false,
                    ]);
                }
            }

            return $usuario;
        });

        // Mostrar credenciales al admin por si el correo no llega
        session()->flash('dev_credenciales', [
            'email'    => $usuario->email,
            'password' => $passwordPlano,
            'nombre'   => $usuario->nombre_usuario,
        ]);

        // Enviar correo de bienvenida
        Log::info("Intentando enviar correo a {$usuario->email} usando " . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port') . ' con ' . config('mail.mailers.smtp.username'));
        try {
            Mail::to($usuario->email)->send(new BienvenidaUsuario($usuario, $passwordPlano));
            Log::info("Correo enviado exitosamente a {$usuario->email}");
        } catch (\Exception $e) {
            Log::error("Error enviar correo a {$usuario->email}: " . $e->getMessage());
        }

        return redirect()->route('admin.usuarios.index')
            ->with('status', "Cuenta de {$usuario->nombre_usuario} creada correctamente.");
    }

    // CU05 F3 (editar): mostrar formulario
    public function edit(int $id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        $roles   = Rol::where('estado_activo', true)->orderBy('nombre_rol')->get();
        return view('admin.usuarios.editar', compact('usuario', 'roles'));
    }

    // CU05 F3/F4/F5/F6/F7: actualizar usuario
    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        $datos = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:120'],
            'email'          => ['required', 'email', 'max:120', Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario')],
            'ci'             => ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'ci')->ignore($id, 'id_usuario')],
            'id_rol'         => ['required', 'exists:roles,id_rol'],
            'estado'         => ['required', Rule::in(['activo', 'inactivo'])],
            'password'       => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'  => 'El correo ya está registrado en otra cuenta. (E1: Usuario duplicado)',
            'ci.unique'     => 'El CI ya está registrado en otra cuenta. (E1: Usuario duplicado)',
            'id_rol.exists' => 'El rol seleccionado no es válido. (E2: Rol inválido)',
        ]);

        $cambios = [
            'nombre_usuario' => $datos['nombre_usuario'],
            'email'          => strtolower(trim($datos['email'])),
            'ci'             => $datos['ci'] ?? null,
            'id_rol'         => $datos['id_rol'],
            'estado'         => $datos['estado'],
        ];

        if (!empty($datos['password'])) {
            $cambios['password_hash'] = Hash::make($datos['password']);
        }

        $usuario->forceFill($cambios)->save();

        $this->bitacoraService->registrar(
            Auth::id(),
            'UPDATE',
            'usuarios',
            "Cuenta actualizada: {$usuario->email}, rol ID {$usuario->id_rol}, estado {$usuario->estado}",
            $request->ip(),
            $usuario->id_usuario
        );

        return redirect()->route('admin.usuarios.index')
            ->with('status', "Cuenta de {$usuario->nombre_usuario} actualizada correctamente.");
    }

    // Activar/desactivar (toggle de estado)
    public function toggleEstado(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id_usuario === Auth::id()) {
            return back()->withErrors(['general' => 'No podés desactivar tu propia cuenta.']);
        }

        $nuevoEstado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->forceFill(['estado' => $nuevoEstado])->save();

        $this->bitacoraService->registrar(
            Auth::id(),
            'UPDATE',
            'usuarios',
            "Estado de cuenta {$usuario->email} cambiado a {$nuevoEstado}",
            $request->ip(),
            $usuario->id_usuario
        );

        return back()->with('status', "Cuenta {$usuario->nombre_usuario} ahora está {$nuevoEstado}.");
    }

    // Eliminar usuario
    public function destroy(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id_usuario === Auth::id()) {
            return back()->withErrors(['general' => 'No podés eliminar tu propia cuenta.']);
        }

        $email = $usuario->email;

        $usuario->delete();

        $this->bitacoraService->registrar(
            Auth::id(),
            'DELETE',
            'usuarios',
            "Cuenta eliminada: {$email}",
            $request->ip(),
            $id
        );

        return back()->with('status', "Cuenta de {$usuario->nombre_usuario} eliminada correctamente.");
    }
}

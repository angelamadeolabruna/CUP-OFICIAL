<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaUsuario;
use App\Models\Carrera;
use App\Models\GestionAdmision;
use App\Models\Postulante;
use App\Models\Prepostulante;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UsuarioImportController extends Controller
{
    // Columnas requeridas en el archivo
    private const COLUMNAS_REQUERIDAS = ['nombre_usuario', 'email', 'ci', 'rol', 'password'];

    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // Mostrar formulario de importación
    public function showForm()
    {
        $roles = Rol::where('estado_activo', true)->orderBy('nombre_rol')->get();
        return view('admin.usuarios.importar', compact('roles'));
    }

    // Procesar archivo CSV o Excel (CU06 F2-F6) y enviar credenciales
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:2048'],
        ], [
            'archivo.mimes' => 'El archivo debe ser CSV o Excel (.csv, .xlsx, .xls). (E1: Formato inválido)',
            'archivo.max'   => 'El archivo no debe superar 2 MB.',
        ]);

        $archivo = $request->file('archivo');
        $ext     = strtolower($archivo->getClientOriginalExtension());

        // CU06 F3: validar estructura del archivo
        try {
            $filas = $ext === 'csv' || $ext === 'txt'
                ? $this->leerCsv($archivo->getRealPath())
                : $this->leerExcel($archivo->getRealPath());
        } catch (\Exception $e) {
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        if (empty($filas)) {
            return back()->withErrors(['archivo' => 'El archivo está vacío o no tiene datos válidos.']);
        }

        // Validar cabeceras
        $cabeceras = array_keys($filas[0]);
        $faltantes = array_diff(self::COLUMNAS_REQUERIDAS, $cabeceras);
        if (!empty($faltantes)) {
            return back()->withErrors([
                'archivo' => 'Columnas faltantes: ' . implode(', ', $faltantes) . '. (E1: Formato inválido)',
            ]);
        }

        // Precargar roles disponibles
        $rolesMap = Rol::where('estado_activo', true)
            ->pluck('id_rol', 'nombre_rol')
            ->toArray();

        // Convertir llaves a minúsculas
        $rolesMap = array_change_key_case($rolesMap, CASE_LOWER);

        // Mapa nombre_carrera → id_carrera (insensible a mayúsculas)
        $carrerasMap = Carrera::where('estado_activo', true)
            ->pluck('id_carrera', 'nombre_carrera')
            ->toArray();
        $carrerasMap = array_change_key_case($carrerasMap, CASE_LOWER);

        $creados         = 0;
        $correosEnviados = 0;
        $errores         = [];
        $devCredenciales = [];

        // CU06 F4/F5: procesar cada fila
        foreach ($filas as $nro => $fila) {
            $linea = $nro + 2; // +2 porque la fila 1 es cabecera

            $nombreUsuario = trim($fila['nombre_usuario'] ?? '');
            $email         = strtolower(trim($fila['email'] ?? ''));
            $ci            = trim($fila['ci'] ?? '') ?: null;
            $rolNombre     = strtolower(trim($fila['rol'] ?? ''));
            $password      = trim($fila['password'] ?? '');

            // Validaciones por fila
            if (!$nombreUsuario || !$email || !$password) {
                $errores[] = "Fila {$linea}: campos nombre_usuario, email o password vacíos.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Fila {$linea}: correo '{$email}' no tiene formato válido.";
                continue;
            }

            if (strlen($password) < 8) {
                $errores[] = "Fila {$linea}: la contraseña debe tener mínimo 8 caracteres.";
                continue;
            }

            // E2: usuario duplicado
            if (Usuario::whereRaw('lower(email) = ?', [$email])->exists()) {
                $errores[] = "Fila {$linea}: el correo '{$email}' ya existe. (E2: Usuario duplicado)";
                continue;
            }

            if ($ci && Usuario::where('ci', $ci)->exists()) {
                $errores[] = "Fila {$linea}: el CI '{$ci}' ya existe. (E2: Usuario duplicado)";
                continue;
            }

            // E2: rol inválido
            if (!isset($rolesMap[$rolNombre])) {
                $errores[] = "Fila {$linea}: rol '{$rolNombre}' no reconocido. Roles válidos: " . implode(', ', array_keys($rolesMap));
                continue;
            }

            $usuario = Usuario::create([
                'nombre_usuario' => $nombreUsuario,
                'email'          => $email,
                'ci'             => $ci,
                'id_rol'         => $rolesMap[$rolNombre],
                'password_hash'  => Hash::make($password),
                'estado'         => 'activo',
            ]);

            // Crear prepostulante automáticamente si el rol es prepostulante
            if ($rolNombre === 'prepostulante') {
                $gestion = GestionAdmision::where('estado', 'activa')->first()
                    ?? GestionAdmision::first()
                    ?? GestionAdmision::create([
                        'nombre_gestion' => 'CUP FICCT ' . now()->year,
                        'fecha_inicio' => now()->startOfYear(),
                        'fecha_fin' => now()->endOfYear(),
                        'estado' => 'activa',
                    ]);

                Prepostulante::withoutEvents(fn() => Prepostulante::create([
                    'id_gestion' => $gestion->id_gestion,
                    'correo' => $email,
                    'ci' => $ci ?? 'SIN_CI',
                    'nombres' => $nombreUsuario,
                    'apellidos' => '',
                    'estado_proceso' => 'prepostulado',
                ]));
            }

            if ($rolNombre === 'postulante_oficial') {
                $gestion = GestionAdmision::where('estado', 'activa')->first()
                    ?? GestionAdmision::first()
                    ?? GestionAdmision::create([
                        'nombre_gestion' => 'CUP FICCT ' . now()->year,
                        'fecha_inicio' => now()->startOfYear(),
                        'fecha_fin' => now()->endOfYear(),
                        'estado' => 'activa',
                    ]);

                $prepostulante = Prepostulante::withoutEvents(fn() => Prepostulante::create([
                    'id_gestion' => $gestion->id_gestion,
                    'correo' => $email,
                    'ci' => $ci ?? 'SIN_CI',
                    'nombres' => $nombreUsuario,
                    'apellidos' => trim($fila['apellidos'] ?? ''),
                    'telefono' => trim($fila['telefono'] ?? ''),
                    'estado_proceso' => 'registro_completo',
                ]));

                $carreraPrimera = trim($fila['carrera_primera_opcion'] ?? '');
                $carreraSegunda = trim($fila['carrera_segunda_opcion'] ?? '');
                $tituloBachiller = trim($fila['titulo_bachiller'] ?? '');

                if (!$carreraPrimera || !isset($carrerasMap[strtolower($carreraPrimera)])) {
                    $errores[] = "Fila {$linea}: carrera_primera_opcion '{$carreraPrimera}' no existe en la base de datos.";
                    continue;
                }

                $data = [
                    'id_prepostulante' => $prepostulante->id_prepostulante,
                    'id_gestion' => $gestion->id_gestion,
                    'id_usuario' => $usuario->id_usuario,
                    'correo' => $email,
                    'carrera_primera_opcion' => $carrerasMap[strtolower($carreraPrimera)],
                    'colegio_procedencia' => trim($fila['colegio_procedencia'] ?? ''),
                    'fecha_nacimiento' => trim($fila['fecha_nacimiento'] ?? now()->subYears(18)),
                    'sexo' => trim($fila['sexo'] ?? 'No especificado'),
                    'direccion' => trim($fila['direccion'] ?? ''),
                    'telefono' => trim($fila['telefono'] ?? ''),
                    'ciudad' => trim($fila['ciudad'] ?? ''),
                    'titulo_bachiller' => !empty($tituloBachiller),
                    'estado_postulante' => 'inscrito',
                ];

                if ($carreraSegunda && isset($carrerasMap[strtolower($carreraSegunda)])) {
                    $data['carrera_segunda_opcion'] = $carrerasMap[strtolower($carreraSegunda)];
                }

                Postulante::withoutEvents(fn() => Postulante::create($data));
            }

            $creados++;

            if (app()->environment('local') && count($devCredenciales) < 5) {
                $devCredenciales[] = [
                    'email'    => $email,
                    'password' => $password,
                    'nombre'   => $nombreUsuario,
                ];
            }

            // Enviar correo de bienvenida con credenciales
            try {
                $usuario->load('rol');
                Mail::to($usuario->email)->send(new BienvenidaUsuario($usuario, $password));
                $correosEnviados++;
            } catch (\Exception $e) {
                Log::error("Error al enviar correo de bienvenida a {$usuario->email}: " . $e->getMessage());
                $errores[] = "Fila {$linea}: cuenta creada pero no se pudo enviar el correo de credenciales a '{$email}'.";
            }
        }

        // CU06 F6: bitácora de la importación
        $this->bitacoraService->registrar(
            Auth::id(),
            'IMPORT',
            'usuarios',
            "Importacion masiva: {$creados} cuentas creadas, {$correosEnviados} correos de credenciales enviados, " . count($errores) . ' errores.',
            $request->ip(),
            null
        );

        $respuesta = [
            'import_creados'  => $creados,
            'import_correos'  => $correosEnviados,
            'import_errores'  => $errores,
            'import_total'    => count($filas),
        ];

        if (app()->environment('local') && !empty($devCredenciales)) {
            $respuesta['dev_credenciales'] = $devCredenciales;
        }

        return back()->with($respuesta);
    }

    // ── helpers privados ──────────────────────────────────────────

    private function leerCsv(string $path): array
    {
        $filas    = [];
        $cabecera = null;

        if (($handle = fopen($path, 'r')) === false) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        while (($linea = fgetcsv($handle, 1000, ',')) !== false) {
            if ($cabecera === null) {
                // Normalizar cabeceras: minúsculas y sin espacios
                $cabecera = array_map(fn($c) => strtolower(trim($c)), $linea);
                continue;
            }
            if (count($linea) !== count($cabecera)) {
                continue; // fila malformada, se omite
            }
            $filas[] = array_combine($cabecera, $linea);
        }

        fclose($handle);
        return $filas;
    }

    private function leerExcel(string $path): array
    {
        // Requiere la extensión php-spreadsheet (disponible vía composer)
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException(
                'Para importar Excel instala: composer require phpoffice/phpspreadsheet'
            );
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $hoja        = $spreadsheet->getActiveSheet();
        $datos       = $hoja->toArray(null, true, true, false);

        if (empty($datos)) {
            return [];
        }

        $cabecera = array_map(fn($c) => strtolower(trim((string) $c)), array_shift($datos));
        $filas    = [];

        foreach ($datos as $fila) {
            if (count($fila) !== count($cabecera)) {
                continue;
            }
            $filas[] = array_combine($cabecera, array_map('strval', $fila));
        }

        return $filas;
    }
}

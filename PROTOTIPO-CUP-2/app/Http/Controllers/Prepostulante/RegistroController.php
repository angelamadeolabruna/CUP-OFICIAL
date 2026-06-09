<?php

namespace App\Http\Controllers\Prepostulante;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\DatosRegistroTemporal;
use App\Models\Prepostulante;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistroController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    public function index()
    {
        $prepostulante = Prepostulante::where('correo', Auth::user()->email)->first();
        $carreras = Carrera::where('estado_activo', true)->orderBy('nombre_carrera')->get();

        $datosRegistro = $prepostulante?->datosRegistroTemporal;

        return view('prepostulante.registro.index', compact('prepostulante', 'carreras', 'datosRegistro'));
    }

    public function store(Request $request)
    {
        $prepostulante = Prepostulante::where('correo', Auth::user()->email)->first();

        if (!$prepostulante) {
            return back()->withErrors(['general' => 'No se encontró tu registro de prepostulante.']);
        }

        $request->validate([
            'carrera_primera_opcion' => ['required', 'exists:carreras,id_carrera'],
            'carrera_segunda_opcion' => ['nullable', 'exists:carreras,id_carrera', 'different:carrera_primera_opcion'],
            'fecha_nacimiento' => ['required', 'date', 'before:' . now()->subYears(14)->format('Y-m-d')],
            'sexo' => ['required', 'in:masculino,femenino'],
            'direccion' => ['required', 'string', 'max:500'],
            'telefono' => ['required', 'string', 'max:30'],
            'colegio_procedencia' => ['required', 'string', 'max:160'],
            'ciudad' => ['required', 'string', 'max:80'],
            'titulo_bachiller' => ['boolean'],
        ], [
            'carrera_primera_opcion.required' => 'Debés seleccionar una carrera como primera opción.',
            'carrera_segunda_opcion.different' => 'La segunda opción debe ser diferente a la primera.',
            'fecha_nacimiento.before' => 'Debés tener al menos 14 años.',
            'sexo.in' => 'Seleccioná masculino o femenino.',
        ]);

        try {
            DB::beginTransaction();

            DatosRegistroTemporal::updateOrCreate(
                ['id_prepostulante' => $prepostulante->id_prepostulante],
                [
                    'carrera_primera_opcion' => $request->carrera_primera_opcion,
                    'carrera_segunda_opcion' => $request->carrera_segunda_opcion,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'sexo' => $request->sexo,
                    'direccion' => $request->direccion,
                    'telefono' => $request->telefono,
                    'correo' => $prepostulante->correo,
                    'colegio_procedencia' => $request->colegio_procedencia,
                    'ciudad' => $request->ciudad,
                    'titulo_bachiller' => $request->boolean('titulo_bachiller'),
                ]
            );

            $this->bitacoraService->registrar(
                Auth::id(),
                'CREATE',
                'datos_registro_temporal',
                "Datos de registro completados para prepostulante {$prepostulante->correo}",
                $request->ip(),
                $prepostulante->id_prepostulante
            );

            DB::commit();

            return back()->with('status', 'Datos de registro guardados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar datos de registro: " . $e->getMessage());
            return back()->withErrors(['general' => 'Error al guardar los datos.']);
        }
    }
}

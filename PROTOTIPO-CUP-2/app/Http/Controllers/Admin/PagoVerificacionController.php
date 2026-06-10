<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Postulante;
use App\Models\Prepostulante;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\EmailService;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoVerificacionController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    public function index(Request $request)
    {
        $query = Prepostulante::with(['pagos' => function ($q) {
            $q->orderByDesc('created_at');
        }, 'datosRegistroTemporal'])
            ->orderByDesc('created_at');

        // Filtro por estado de pago
        if ($filtroPago = $request->input('estado_pago')) {
            if ($filtroPago === 'sin_pago') {
                $query->whereDoesntHave('pagos');
            } else {
                $query->whereHas('pagos', function ($q) use ($filtroPago) {
                    $q->where('estado_pago', $filtroPago);
                });
            }
        }

        // Filtro por texto (nombre, CI, correo)
        if ($busqueda = $request->input('busqueda')) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('lower(nombres) like ?', ['%' . strtolower($busqueda) . '%'])
                  ->orWhereRaw('lower(apellidos) like ?', ['%' . strtolower($busqueda) . '%'])
                  ->orWhereRaw('lower(correo) like ?', ['%' . strtolower($busqueda) . '%'])
                  ->orWhere('ci', 'like', "%{$busqueda}%");
            });
        }

        $prepostulantes = $query->paginate(20)->withQueryString();

        return view('admin.pagos.index', compact('prepostulantes'));
    }

    public function confirmarPago(Request $request, int $id)
    {
        $pago = Pago::with('prepostulante')->findOrFail($id);

        if ($pago->estado_pago !== 'pendiente') {
            return back()->withErrors(['general' => 'E1: El pago ya fue procesado (estado: ' . $pago->estado_pago . ').']);
        }

        try {
            DB::beginTransaction();

            $pago->forceFill(['estado_pago' => 'confirmado'])->save();

            if ($pago->prepostulante) {
                $pago->prepostulante->forceFill(['estado_proceso' => 'pago_confirmado'])->save();
            }

            $this->bitacoraService->registrar(
                Auth::id(),
                'UPDATE',
                'pagos',
                "Pago confirmado: {$pago->codigo_pago}, prepostulante {$pago->prepostulante?->correo}",
                $request->ip(),
                $pago->id_pago
            );

            DB::commit();

            return back()->with('status', "Pago {$pago->codigo_pago} confirmado correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al confirmar pago {$pago->codigo_pago}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Error al confirmar el pago.']);
        }
    }

    public function rechazarPago(Request $request, int $id)
    {
        $pago = Pago::with('prepostulante')->findOrFail($id);

        if ($pago->estado_pago !== 'pendiente') {
            return back()->withErrors(['general' => 'E1: El pago ya fue procesado (estado: ' . $pago->estado_pago . ').']);
        }

        $request->validate([
            'motivo_rechazo' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $pago->forceFill(['estado_pago' => 'rechazado'])->save();

            $this->bitacoraService->registrar(
                Auth::id(),
                'UPDATE',
                'pagos',
                "Pago rechazado: {$pago->codigo_pago}, prepostulante {$pago->prepostulante?->correo}, motivo: " . ($request->motivo_rechazo ?? 'Sin especificar'),
                $request->ip(),
                $pago->id_pago
            );

            DB::commit();

            return back()->with('status', "Pago {$pago->codigo_pago} rechazado.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al rechazar pago {$pago->codigo_pago}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Error al rechazar el pago.']);
        }
    }

    public function confirmarPostulante(Request $request, int $idPrepostulante)
    {
        $prepostulante = Prepostulante::with('pagos', 'datosRegistroTemporal')->findOrFail($idPrepostulante);
        $datosRegistro = $prepostulante->datosRegistroTemporal;

        if (!$datosRegistro) {
            return back()->withErrors(['general' => 'El prepostulante no ha completado sus datos de registro.']);
        }

        $pagoConfirmado = $prepostulante->pagos->where('estado_pago', 'confirmado')->first();
        if (!$pagoConfirmado) {
            return back()->withErrors(['general' => 'El prepostulante debe tener un pago confirmado.']);
        }

        if ($prepostulante->postulante) {
            return back()->withErrors(['general' => 'Este prepostulante ya fue convertido a postulante.']);
        }

        try {
            DB::beginTransaction();

            $usuario = Usuario::where('email', $prepostulante->correo)->first();

            $postulante = Postulante::create([
                'id_prepostulante' => $prepostulante->id_prepostulante,
                'id_usuario' => $usuario?->id_usuario,
                'id_gestion' => $prepostulante->id_gestion,
                'carrera_primera_opcion' => $datosRegistro->carrera_primera_opcion,
                'carrera_segunda_opcion' => $datosRegistro->carrera_segunda_opcion,
                'fecha_nacimiento' => $datosRegistro->fecha_nacimiento,
                'sexo' => $datosRegistro->sexo,
                'direccion' => $datosRegistro->direccion,
                'telefono' => $datosRegistro->telefono,
                'correo' => $datosRegistro->correo,
                'colegio_procedencia' => $datosRegistro->colegio_procedencia,
                'ciudad' => $datosRegistro->ciudad,
                'titulo_bachiller' => $datosRegistro->titulo_bachiller,
                'doc_identidad_url' => $datosRegistro->doc_identidad_url,
                'doc_titulo_url' => $datosRegistro->doc_titulo_url,
                'estado_postulante' => 'inscrito',
            ]);

            $prepostulante->forceFill(['estado_proceso' => 'postulante_completo'])->save();

            if ($usuario) {
                $rolPostulante = Rol::where('nombre_rol', 'postulante_oficial')->first();
                if ($rolPostulante) {
                    $usuario->forceFill(['id_rol' => $rolPostulante->id_rol])->save();
                }
            }

            $this->bitacoraService->registrar(
                Auth::id(),
                'CREATE',
                'postulantes',
                "Postulante creado: {$prepostulante->correo}",
                $request->ip(),
                $postulante->id_postulante
            );

            DB::commit();

            if ($usuario) {
                $html = view('emails.postulante-confirmado', [
                    'usuario' => $usuario,
                    'prepostulante' => $prepostulante,
                ])->render();
                EmailService::enviar($usuario->email, 'Postulación Confirmada — Bienvenido al CUP FICCT', $html);
            }

            return back()->with('status', "Postulante {$prepostulante->nombres} confirmado exitosamente. Se envió correo con sus nuevas credenciales.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al confirmar postulante: " . $e->getMessage());
            return back()->withErrors(['general' => 'Error al confirmar el postulante: ' . $e->getMessage()]);
        }
    }
}

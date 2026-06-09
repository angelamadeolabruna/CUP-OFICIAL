<?php

namespace App\Http\Controllers\Prepostulante;

use App\Http\Controllers\Controller;
use App\Models\GestionAdmision;
use App\Models\Pago;
use App\Models\Prepostulante;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    private function obtenerPrepostulante(): Prepostulante
    {
        $usuario = Auth::user();

        $prepostulante = Prepostulante::where('correo', $usuario->email)->first();

        if (!$prepostulante) {
            $gestion = GestionAdmision::where('estado', 'activa')->first()
                ?? GestionAdmision::first()
                ?? GestionAdmision::create([
                    'nombre_gestion' => 'CUP FICCT ' . now()->year,
                    'fecha_inicio' => now()->startOfYear(),
                    'fecha_fin' => now()->endOfYear(),
                    'estado' => 'activa',
                ]);

            $prepostulante = Prepostulante::create([
                'id_gestion' => $gestion->id_gestion,
                'correo' => $usuario->email,
                'ci' => $usuario->ci ?? 'SIN_CI',
                'nombres' => $usuario->nombre_usuario,
                'apellidos' => '',
                'estado_proceso' => 'prepostulado',
            ]);
        }

        return $prepostulante;
    }

    public function index()
    {
        $prepostulante = $this->obtenerPrepostulante();
        $pagos = $prepostulante->pagos()->orderByDesc('created_at')->get();
        $totalPagado = $pagos->whereIn('estado_pago', ['confirmado', 'pendiente'])->sum('monto');

        return view('prepostulante.pagos.index', compact('prepostulante', 'pagos', 'totalPagado'));
    }

    public function store(Request $request)
    {
        $prepostulante = $this->obtenerPrepostulante();

        $request->validate([
            'metodo_pago' => ['required', 'string', 'in:transferencia,deposito,tigo_money,QR,paypal'],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'],
        ]);

        $monto = 120.00;
        $codigoPago = 'PAG-' . strtoupper(Str::random(8));

        try {
            DB::beginTransaction();

            $pago = Pago::create([
                'id_prepostulante' => $prepostulante->id_prepostulante,
                'codigo_pago' => $codigoPago,
                'monto' => $monto,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => $request->metodo_pago === 'paypal' ? 'confirmado' : 'pendiente',
                'comprobante_url' => null,
            ]);

            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('comprobantes', 'public');
                $pago->forceFill(['comprobante_url' => $path])->save();
            }

            $this->bitacoraService->registrar(
                Auth::id(),
                'CREATE',
                'pagos',
                "Pago registrado: {$codigoPago}, monto {$monto}, método {$request->metodo_pago}",
                $request->ip(),
                $pago->id_pago
            );

            DB::commit();

            $mensaje = $request->metodo_pago === 'paypal'
                ? "Pago procesado correctamente vía PayPal. Código: {$codigoPago}."
                : "Pago registrado correctamente. Código: {$codigoPago}. Estado: Pendiente de confirmación.";

            return back()->with('status', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar pago: " . $e->getMessage());
            return back()->withErrors(['general' => 'E3: Error al procesar el pago. Intentalo de nuevo.']);
        }
    }
}

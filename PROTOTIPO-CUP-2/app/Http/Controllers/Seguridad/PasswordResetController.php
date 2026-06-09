<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\TokenRecuperacion;
use App\Models\Usuario;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    // Paso 1: mostrar formulario de solicitud
    public function showSolicitud()
    {
        return view('seguridad.recuperar-password');
    }

    // Paso 1: procesar solicitud (genera token y "envía" correo)
    public function solicitar(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email', 'max:120'],
        ]);

        // Siempre responder igual para no revelar si el correo existe (seguridad)
        $mensajeGenerico = 'Si el correo está registrado, recibirás las instrucciones para restablecer tu contraseña.';

        $usuario = Usuario::where('estado', 'activo')
            ->whereRaw('lower(email) = ?', [strtolower(trim($datos['email']))])
            ->first();

        if (!$usuario) {
            return back()->with('status', $mensajeGenerico);
        }

        // Invalidar tokens previos no usados
        TokenRecuperacion::where('id_usuario', $usuario->id_usuario)
            ->where('usado', false)
            ->update(['usado' => true]);

        // Generar token seguro
        $tokenPlano = Str::random(64);
        $tokenHash  = Hash::make($tokenPlano);

        TokenRecuperacion::create([
            'id_usuario'  => $usuario->id_usuario,
            'codigo_hash' => $tokenHash,
            'usado'       => false,
            'expira_en'   => now()->addMinutes(60),
        ]);

        // En producción aquí se enviaría el email con $tokenPlano.
        // Para desarrollo lo exponemos en sesión flash (solo modo local).
        if (app()->environment('local') || config('app.debug')) {
            session(['dev_reset_token' => $tokenPlano, 'dev_reset_email' => $usuario->email]);
        }

        $this->bitacoraService->registrar(
            $usuario->id_usuario,
            'PASSWORD_RESET_SOLICITADO',
            'tokens_recuperacion',
            'Solicitud de recuperacion de contrasena',
            $request->ip(),
            $usuario->id_usuario
        );

        return back()->with('status', $mensajeGenerico);
    }

    // Paso 2: mostrar formulario de nueva contraseña
    public function showReset(string $token)
    {
        return view('seguridad.nueva-password', ['token' => $token]);
    }

    // Paso 2: procesar la nueva contraseña
    public function resetear(Request $request)
    {
        $datos = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = Usuario::where('estado', 'activo')
            ->whereRaw('lower(email) = ?', [strtolower(trim($datos['email']))])
            ->first();

        if (!$usuario) {
            return back()->withErrors(['email' => 'Correo no encontrado o cuenta inactiva.']);
        }

        // Buscar token válido y no expirado
        $registro = TokenRecuperacion::where('id_usuario', $usuario->id_usuario)
            ->where('usado', false)
            ->where('expira_en', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (!$registro || !Hash::check($datos['token'], $registro->codigo_hash)) {
            return back()->withErrors(['token' => 'El enlace de recuperación es inválido o ya expiró.']);
        }

        // Marcar token como usado
        $registro->update(['usado' => true]);

        // Actualizar contraseña
        $usuario->forceFill([
            'password_hash' => Hash::make($datos['password']),
        ])->save();

        $this->bitacoraService->registrar(
            $usuario->id_usuario,
            'PASSWORD_RESET_COMPLETADO',
            'usuarios',
            'Contrasena restablecida via recuperacion',
            $request->ip(),
            $usuario->id_usuario
        );

        return redirect()->route('login')
            ->with('status', 'Contraseña actualizada correctamente. Ya podés iniciar sesión.');
    }
}

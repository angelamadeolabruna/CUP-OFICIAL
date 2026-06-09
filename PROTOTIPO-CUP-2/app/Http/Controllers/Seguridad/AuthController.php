<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Seguridad\BitacoraService;
use App\Services\Seguridad\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly SupabaseAuthService $authService,
        private readonly BitacoraService $bitacoraService
    ) {
    }

    public function showLogin()
    {
        return view('seguridad.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'credencial' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $credencial = strtolower(trim($credenciales['credencial']));

        $usuario = Usuario::with('rol')
            ->whereRaw('lower(email) = ?', [$credencial])
            ->orWhere('ci', $credencial)
            ->first();

        if (!$usuario || $usuario->estado !== 'activo') {
            return back()->withErrors([
                'credencial' => 'Usuario no encontrado o inactivo.',
            ])->onlyInput('credencial');
        }

        if (!$this->authService->validarCredenciales($usuario, $credenciales['password'])) {
            return back()->withErrors([
                'password' => 'Credenciales invalidas.',
            ])->onlyInput('credencial');
        }

        Auth::login($usuario);

        $usuario->forceFill([
            'ultimo_login' => now(),
        ])->save();

        session([
            'rol' => $usuario->rol?->nombre_rol,
            'permisos' => $usuario->rol?->permisos_json ?? [],
        ]);

        $this->bitacoraService->registrar(
            $usuario->id_usuario,
            'LOGIN',
            'usuarios',
            'Inicio de sesion en la plataforma',
            $request->ip(),
            $usuario->id_usuario
        );

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->bitacoraService->registrar(
                Auth::id(),
                'LOGOUT',
                'usuarios',
                'Cierre de sesion',
                $request->ip(),
                Auth::id()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

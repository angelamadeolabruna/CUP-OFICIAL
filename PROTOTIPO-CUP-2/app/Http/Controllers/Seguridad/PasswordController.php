<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Services\Seguridad\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function __construct(private readonly BitacoraService $bitacoraService)
    {
    }

    public function edit()
    {
        return view('seguridad.cambiar-password');
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = Auth::user();

        if (!Hash::check($datos['password_actual'], $usuario->password_hash)) {
            return back()->withErrors([
                'password_actual' => 'La contrasena actual no es correcta.',
            ]);
        }

        $usuario->forceFill([
            'password_hash' => Hash::make($datos['password']),
        ])->save();

        $this->bitacoraService->registrar(
            $usuario->id_usuario,
            'UPDATE',
            'usuarios',
            'Cambio de contrasena propia',
            $request->ip(),
            $usuario->id_usuario
        );

        return redirect()->route('dashboard')->with('status', 'Contrasena actualizada correctamente.');
    }
}

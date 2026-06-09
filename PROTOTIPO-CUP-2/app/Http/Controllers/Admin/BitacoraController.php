<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $query = Bitacora::with('usuario')->orderByDesc('created_at');

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->tabla);
        }

        if ($request->filled('desde')) {
            $query->where('created_at', '>=', $request->desde . ' 00:00:00');
        }

        if ($request->filled('hasta')) {
            $query->where('created_at', '<=', $request->hasta . ' 23:59:59');
        }

        $eventos = $query->paginate(50);

        $usuarios = Usuario::orderBy('nombre_usuario')->get();
        $acciones = Bitacora::select('accion')->distinct()->orderBy('accion')->pluck('accion');
        $tablas = Bitacora::select('tabla_afectada')->distinct()->whereNotNull('tabla_afectada')->orderBy('tabla_afectada')->pluck('tabla_afectada');

        $conteo = [
            'total' => Bitacora::count(),
            'hoy' => Bitacora::whereDate('created_at', today())->count(),
            'acciones_distintas' => $acciones->count(),
        ];

        return view('admin.bitacora', compact('eventos', 'usuarios', 'acciones', 'tablas', 'conteo'));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->rol) {
            abort(403, 'Acceso denegado.');
        }

        if (!in_array($usuario->rol->nombre_rol, $roles, true)) {
            abort(403, 'Acceso denegado para el rol actual.');
        }

        return $next($request);
    }
}

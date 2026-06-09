<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolPrepostulante
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->rol || $usuario->rol->nombre_rol !== 'prepostulante') {
            abort(403, 'Acceso exclusivo para prepostulantes.');
        }

        return $next($request);
    }
}

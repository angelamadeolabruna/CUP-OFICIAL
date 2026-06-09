<?php

namespace App\Services\Seguridad;

use App\Models\Bitacora;

class BitacoraService
{
    public function registrar(?int $idUsuario, string $accion, ?string $tabla, ?string $detalle, ?string $ip = null, ?int $idRegistro = null): Bitacora
    {
        return Bitacora::registrarEvento($idUsuario, $accion, $tabla, $detalle, $ip, $idRegistro);
    }
}

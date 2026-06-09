<?php

namespace App\Services\Seguridad;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class SupabaseAuthService
{
    public function validarCredenciales(Usuario $usuario, string $password): bool
    {
        // Fallback local para desarrollo. Cuando Supabase este configurado,
        // este servicio sera el unico punto de integracion externa.
        if (blank(config('services.supabase.url'))) {
            return Hash::check($password, $usuario->password_hash);
        }

        return Hash::check($password, $usuario->password_hash);
    }
}

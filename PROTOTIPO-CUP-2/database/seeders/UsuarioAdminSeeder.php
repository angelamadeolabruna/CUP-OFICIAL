<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $rol = DB::table('roles')->where('nombre_rol', 'administrador')->first();

        if (!$rol) {
            return;
        }

        DB::table('usuarios')->updateOrInsert(
            ['email' => 'admin@cup.test'],
            [
                'id_rol' => $rol->id_rol,
                'ci' => '0000000',
                'nombre_usuario' => 'Administrador CUP',
                'password_hash' => Hash::make('admin123456'),
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

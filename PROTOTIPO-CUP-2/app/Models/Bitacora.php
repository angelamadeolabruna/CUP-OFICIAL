<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';
    protected $primaryKey = 'id_bitacora';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'tabla_afectada',
        'id_registro',
        'detalle',
        'ip_origen',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public static function registrarEvento(?int $idUsuario, string $accion, ?string $tabla, ?string $detalle, ?string $ip = null, ?int $idRegistro = null): self
    {
        return self::create([
            'id_usuario' => $idUsuario,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'id_registro' => $idRegistro,
            'detalle' => $detalle,
            'ip_origen' => $ip,
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';
    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'codigo_carrera',
        'nombre_carrera',
        'estado_activo',
    ];

    protected $casts = [
        'estado_activo' => 'boolean',
    ];
}

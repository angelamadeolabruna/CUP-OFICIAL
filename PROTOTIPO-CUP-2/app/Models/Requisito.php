<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    protected $table = 'requisitos';
    protected $primaryKey = 'id_requisito';

    protected $fillable = [
        'id_gestion',
        'nombre_requisito',
        'descripcion',
        'obligatorio',
        'estado_activo',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'estado_activo' => 'boolean',
    ];
}

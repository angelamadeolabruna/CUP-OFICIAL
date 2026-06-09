<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    protected $table = 'resultados';
    protected $primaryKey = 'id_resultado';

    protected $fillable = [
        'id_postulante',
        'promedio_final',
        'estado_academico',
        'publicado',
        'fecha_calculo',
        'fecha_publicacion',
    ];

    protected $casts = [
        'promedio_final' => 'decimal:2',
        'publicado' => 'boolean',
        'fecha_calculo' => 'datetime',
        'fecha_publicacion' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosRegistroTemporal extends Model
{
    protected $table = 'datos_registro_temporal';
    protected $primaryKey = 'id_datos_registro';

    protected $fillable = [
        'id_prepostulante',
        'carrera_primera_opcion',
        'carrera_segunda_opcion',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'correo',
        'colegio_procedencia',
        'ciudad',
        'titulo_bachiller',
        'doc_identidad_url',
        'doc_titulo_url',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'titulo_bachiller' => 'boolean',
    ];
}

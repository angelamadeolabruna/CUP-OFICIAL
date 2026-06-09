<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    protected $table = 'postulantes';
    protected $primaryKey = 'id_postulante';

    protected $fillable = [
        'id_prepostulante',
        'id_usuario',
        'id_gestion',
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
        'estado_postulante',
        'promedio_final',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'titulo_bachiller' => 'boolean',
        'promedio_final' => 'decimal:2',
    ];

    public function prepostulante()
    {
        return $this->belongsTo(Prepostulante::class, 'id_prepostulante', 'id_prepostulante');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function resultado()
    {
        return $this->hasOne(Resultado::class, 'id_postulante', 'id_postulante');
    }

    public function admision()
    {
        return $this->hasOne(Admision::class, 'id_postulante', 'id_postulante');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'id_postulante', 'id_postulante');
    }

    public function primeraOpcion()
    {
        return $this->belongsTo(Carrera::class, 'carrera_primera_opcion', 'id_carrera');
    }

    public function segundaOpcion()
    {
        return $this->belongsTo(Carrera::class, 'carrera_segunda_opcion', 'id_carrera');
    }
}

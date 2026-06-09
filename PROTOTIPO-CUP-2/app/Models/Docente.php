<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';
    protected $primaryKey = 'id_docente';

    protected $fillable = [
        'id_usuario',
        'ci',
        'nombres',
        'apellidos',
        'profesion',
        'correo',
        'telefono',
        'estado_docente',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function requisitos()
    {
        return $this->hasMany(RequisitoDocente::class, 'id_docente', 'id_docente');
    }

    public function cargasHorarias()
    {
        return $this->hasMany(CargaHoraria::class, 'id_docente', 'id_docente');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}

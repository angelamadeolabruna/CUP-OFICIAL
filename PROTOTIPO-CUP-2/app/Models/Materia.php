<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id_materia';

    protected $fillable = [
        'nombre_materia',
        'estado_activo',
    ];

    protected $casts = [
        'estado_activo' => 'boolean',
    ];

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_materia', 'id_materia');
    }
}

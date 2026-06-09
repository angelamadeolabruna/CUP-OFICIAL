<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';
    protected $primaryKey = 'id_evaluacion';

    protected $fillable = [
        'id_gestion',
        'id_materia',
        'numero_evaluacion',
        'porcentaje',
        'fecha_evaluacion',
        'estado',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'fecha_evaluacion' => 'date',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAdmision::class, 'id_gestion', 'id_gestion');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'id_evaluacion', 'id_evaluacion');
    }
}

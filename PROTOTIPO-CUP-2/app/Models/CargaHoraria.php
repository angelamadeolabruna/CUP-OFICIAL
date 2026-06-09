<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaHoraria extends Model
{
    protected $table = 'cargas_horarias';
    protected $primaryKey = 'id_carga_horaria';

    protected $fillable = [
        'id_docente',
        'id_grupo',
        'id_materia',
        'id_horario',
        'estado',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }
}

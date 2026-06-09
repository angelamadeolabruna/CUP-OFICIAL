<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoHorario extends Model
{
    protected $table = 'grupo_horarios';
    protected $primaryKey = 'id_grupo_horario';

    protected $fillable = [
        'id_grupo',
        'id_horario',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }
}

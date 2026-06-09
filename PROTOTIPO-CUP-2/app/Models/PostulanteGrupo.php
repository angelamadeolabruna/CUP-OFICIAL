<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostulanteGrupo extends Model
{
    protected $table = 'postulante_grupos';
    protected $primaryKey = 'id_postulante_grupo';

    protected $fillable = [
        'id_postulante',
        'id_grupo',
        'fecha_asignacion',
        'estado',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante', 'id_postulante');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }
}

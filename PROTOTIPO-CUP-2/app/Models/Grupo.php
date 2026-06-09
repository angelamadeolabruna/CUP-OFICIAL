<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'id_gestion',
        'id_materia',
        'id_aula',
        'nombre_grupo',
        'capacidad_maxima',
        'estado',
    ];

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'id_aula');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAdmision::class, 'id_gestion', 'id_gestion');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function horarios()
    {
        return $this->hasMany(GrupoHorario::class, 'id_grupo', 'id_grupo');
    }

    public function postulantes()
    {
        return $this->hasMany(PostulanteGrupo::class, 'id_grupo', 'id_grupo');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapacidadAula extends Model
{
    protected $table = 'capacidades_aula';
    protected $primaryKey = 'id_capacidad';

    protected $fillable = [
        'id_gestion',
        'max_estudiantes',
        'descripcion',
    ];

    public function gestion()
    {
        return $this->belongsTo(GestionAdmision::class, 'id_gestion', 'id_gestion');
    }
}

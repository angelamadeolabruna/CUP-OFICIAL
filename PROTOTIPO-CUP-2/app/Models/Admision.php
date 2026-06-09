<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admision extends Model
{
    protected $table = 'admisiones';
    protected $primaryKey = 'id_admision';
    public $timestamps = false;

    protected $fillable = [
        'id_postulante',
        'id_resultado',
        'id_carrera_asignada',
        'opcion_asignada',
        'orden_merito',
        'estado_admision',
        'created_at',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante', 'id_postulante');
    }

    public function resultado()
    {
        return $this->belongsTo(Resultado::class, 'id_resultado', 'id_resultado');
    }

    public function carreraAsignada()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_asignada', 'id_carrera');
    }
}

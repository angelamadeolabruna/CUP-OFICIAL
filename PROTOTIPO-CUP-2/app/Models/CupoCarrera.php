<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupoCarrera extends Model
{
    protected $table = 'cupos_carrera';
    protected $primaryKey = 'id_cupo';

    protected $fillable = [
        'id_gestion',
        'id_carrera',
        'cupos_totales',
        'cupos_ocupados',
    ];

    protected $casts = [
        'cupos_totales' => 'integer',
        'cupos_ocupados' => 'integer',
    ];

    public function gestion()
    {
        return $this->belongsTo(GestionAdmision::class, 'id_gestion', 'id_gestion');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function getCuposDisponiblesAttribute()
    {
        return $this->cupos_totales - $this->cupos_ocupados;
    }
}

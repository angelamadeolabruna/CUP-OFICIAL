<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prepostulante extends Model
{
    protected $table = 'prepostulantes';
    protected $primaryKey = 'id_prepostulante';

    protected $fillable = [
        'id_gestion',
        'ci',
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'estado_proceso',
    ];

    public function gestion()
    {
        return $this->belongsTo(GestionAdmision::class, 'id_gestion', 'id_gestion');
    }

    public function requisitosPresentados()
    {
        return $this->hasMany(RequisitoPresentado::class, 'id_prepostulante', 'id_prepostulante');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_prepostulante', 'id_prepostulante');
    }

    public function datosRegistroTemporal()
    {
        return $this->hasOne(DatosRegistroTemporal::class, 'id_prepostulante', 'id_prepostulante');
    }

    public function postulante()
    {
        return $this->hasOne(Postulante::class, 'id_prepostulante', 'id_prepostulante');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaPostulante extends Model
{
    protected $table = 'bajas_postulante';
    protected $primaryKey = 'id_baja';
    public $timestamps = false;

    protected $fillable = [
        'id_postulante',
        'id_usuario',
        'motivo',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_baja' => 'datetime',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante', 'id_postulante');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}

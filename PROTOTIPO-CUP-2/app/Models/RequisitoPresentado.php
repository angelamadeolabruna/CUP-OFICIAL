<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoPresentado extends Model
{
    protected $table = 'requisitos_presentados';
    protected $primaryKey = 'id_requisito_presentado';

    protected $fillable = [
        'id_prepostulante',
        'id_requisito',
        'archivo_url',
        'estado_revision',
        'observacion',
        'fecha_presentacion',
        'fecha_revision',
        'revisado_por',
    ];

    protected $casts = [
        'fecha_presentacion' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    public function prepostulante()
    {
        return $this->belongsTo(Prepostulante::class, 'id_prepostulante', 'id_prepostulante');
    }

    public function requisito()
    {
        return $this->belongsTo(Requisito::class, 'id_requisito', 'id_requisito');
    }
}

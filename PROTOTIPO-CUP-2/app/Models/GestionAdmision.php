<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionAdmision extends Model
{
    protected $table = 'gestiones_admision';
    protected $primaryKey = 'id_gestion';

    protected $fillable = [
        'nombre_gestion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_gestion', 'id_gestion');
    }

    public function scopeActiva($query)
    {
        return $query->where('estado', 'activa');
    }
}

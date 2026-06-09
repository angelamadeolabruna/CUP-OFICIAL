<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
    ];
}

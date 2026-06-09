<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoDocente extends Model
{
    protected $table = 'requisitos_docente';
    protected $primaryKey = 'id_requisito_docente';

    protected $fillable = [
        'id_docente',
        'tipo_requisito',
        'archivo_url',
        'estado_revision',
        'observacion',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }
}

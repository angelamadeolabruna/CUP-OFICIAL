<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_prepostulante',
        'codigo_pago',
        'monto',
        'metodo_pago',
        'estado_pago',
        'comprobante_url',
        'referencia_pasarela',
        'fecha_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function prepostulante()
    {
        return $this->belongsTo(Prepostulante::class, 'id_prepostulante', 'id_prepostulante');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenRecuperacion extends Model
{
    protected $table = 'tokens_recuperacion';
    protected $primaryKey = 'id_token';

    protected $fillable = [
        'id_usuario',
        'codigo_hash',
        'usado',
        'expira_en',
    ];

    protected $casts = [
        'usado' => 'boolean',
        'expira_en' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionPublica extends Model
{
    protected $table = 'promociones_publicas';

    protected $fillable = [
        'titulo', 'etiqueta', 'descripcion', 'texto_boton', 'url_boton', 'orden', 'activo',
        'vigencia_inicio', 'vigencia_fin', 'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'vigencia_inicio' => 'datetime',
            'vigencia_fin' => 'datetime',
            'orden' => 'integer',
        ];
    }
}

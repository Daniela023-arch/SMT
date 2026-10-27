<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromocionSuscripcion extends Model
{
    protected $table = 'promociones_suscripcion';

    protected $fillable = [
        'codigo',
        'tipo',
        'valor',
        'vigencia_inicio',
        'vigencia_fin',
        'usos_maximos',
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'vigencia_inicio' => 'datetime',
            'vigencia_fin' => 'datetime',
            'usos_maximos' => 'integer',
            'activo' => 'boolean'
        ];
    }

    public function asignacionesPromocion(): HasMany
    {
        return $this->hasMany(AsignacionPromocion::class, 'promocion_id');
    }
}

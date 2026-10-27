<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionPromocion extends Model
{
    protected $table = 'asignaciones_promocion';

    protected $fillable = [
        'suscripcion_id',
        'promocion_id',
        'aplicada_at'
    ];

    protected function casts(): array
    {
        return [
            'aplicada_at' => 'datetime'
        ];
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(SuscripcionTenant::class, 'suscripcion_id');
    }
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(PromocionSuscripcion::class, 'promocion_id');
    }
}

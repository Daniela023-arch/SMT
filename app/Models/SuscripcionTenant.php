<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuscripcionTenant extends Model
{
    protected $table = 'suscripciones_tenant';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'estado',
        'proveedor',
        'suscripcion_externa_id',
        'inicio_at',
        'fin_at',
        'cancelada_at'
    ];

    protected function casts(): array
    {
        return [
            'inicio_at' => 'datetime',
            'fin_at' => 'datetime',
            'cancelada_at' => 'datetime'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanSuscripcion::class, 'plan_id');
    }
    public function asignacionesPromocion(): HasMany
    {
        return $this->hasMany(AsignacionPromocion::class, 'suscripcion_id');
    }
    public function pagosSuscripcion(): HasMany
    {
        return $this->hasMany(PagoSuscripcion::class, 'suscripcion_id');
    }
}

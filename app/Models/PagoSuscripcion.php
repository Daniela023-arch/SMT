<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagoSuscripcion extends Model
{
    protected $table = 'pagos_suscripcion';

    protected $fillable = [
        'suscripcion_id',
        'monto',
        'moneda',
        'estado',
        'referencia_externa',
        'pagado_at'
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'pagado_at' => 'datetime'
        ];
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(SuscripcionTenant::class, 'suscripcion_id');
    }
}

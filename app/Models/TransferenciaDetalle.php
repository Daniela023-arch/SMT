<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaDetalle extends Model
{
    protected $table = 'transferencia_detalles';

    protected $fillable = [
        'tenant_id',
        'transferencia_id',
        'producto_id',
        'lote_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'cantidad_solicitada',
        'cantidad_enviada',
        'cantidad_recibida',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'decimal:4',
            'cantidad_enviada' => 'decimal:4',
            'cantidad_recibida' => 'decimal:4',
        ];
    }

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(Transferencia::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}

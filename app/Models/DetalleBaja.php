<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleBaja extends Model
{
    protected $table = 'detalles_baja';

    protected $fillable = [
        'tenant_id',
        'baja_id',
        'producto_id',
        'unidad_inventario_id',
        'lote_id',
        'cantidad',
        'observacion'
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function baja(): BelongsTo
    {
        return $this->belongsTo(Baja::class, 'baja_id');
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function unidad_inventario(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id');
    }
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}

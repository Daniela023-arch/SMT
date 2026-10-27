<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovimiento extends Model
{
    protected $table = 'stock_movimientos';

    protected $fillable = [
        'tenant_id',
        'producto_id',
        'lote_id',
        'unidad_inventario_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'usuario_id',
        'tipo',
        'cantidad',
        'referencia_tipo',
        'referencia_id',
        'motivo'
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
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
    public function unidad_inventario(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id');
    }
    public function ubicacion_origen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }
    public function ubicacion_destino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

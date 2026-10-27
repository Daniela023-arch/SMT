<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int         $producto_id
 * @property int|null    $ubicacion_id
 * @property int|null    $lote_id
 * @property string|null $numero_serie
 * @property string|null $codigo_activo
 * @property string      $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UnidadInventario extends Model
{
    protected $table = 'unidades_inventario';

    protected $fillable = [
        'tenant_id',
        'producto_id',
        'ubicacion_id',
        'lote_id',
        'numero_serie',
        'codigo_activo',
        'estado',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function codigosIdentificacion(): HasMany
    {
        return $this->hasMany(CodigoIdentificacion::class, 'unidad_inventario_id');
    }

    public function detalleEquipo(): HasOne
    {
        return $this->hasOne(DetalleEquipo::class, 'unidad_inventario_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'unidad_inventario_id');
    }

    public function detallesPrestamos(): HasMany
    {
        return $this->hasMany(DetallePrestamo::class, 'unidad_inventario_id');
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'unidad_inventario_id');
    }
}

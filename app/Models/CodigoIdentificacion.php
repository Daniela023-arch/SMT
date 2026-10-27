<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int|null    $producto_id
 * @property int|null    $unidad_inventario_id
 * @property string      $tipo
 * @property string      $codigo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodigoIdentificacion extends Model
{
    protected $table = 'codigos_identificacion';

    protected $fillable = [
        'tenant_id',
        'producto_id',
        'unidad_inventario_id',
        'tipo',
        'codigo',
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

    public function unidadInventario(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id');
    }
}

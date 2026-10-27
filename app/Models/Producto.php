<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int|null    $categoria_id
 * @property string      $sku
 * @property string      $nombre
 * @property string|null $descripcion
 * @property string      $unidad_medida
 * @property float|null  $precio_venta
 * @property float       $stock_minimo
 * @property bool        $requiere_serie
 * @property bool        $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'tenant_id',
        'categoria_id',
        'sku',
        'nombre',
        'descripcion',
        'unidad_medida',
        'precio_venta',
        'stock_minimo',
        'requiere_serie',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta'   => 'decimal:2',
            'stock_minimo'   => 'decimal:3',
            'requiere_serie' => 'boolean',
            'activo'         => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function unidadesInventario(): HasMany
    {
        return $this->hasMany(UnidadInventario::class, 'producto_id');
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class, 'producto_id');
    }

    public function codigosIdentificacion(): HasMany
    {
        return $this->hasMany(CodigoIdentificacion::class, 'producto_id');
    }

    public function datosBibliograficos(): HasOne
    {
        return $this->hasOne(DatosBibliograficos::class, 'producto_id');
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedor', 'producto_id', 'proveedor_id')
                    ->withPivot('codigo_proveedor', 'costo_referencia', 'tenant_id')
                    ->withTimestamps();
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'producto_id');
    }
}

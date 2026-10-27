<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int         $producto_id
 * @property string      $numero_lote
 * @property string|null $fecha_fabricacion
 * @property string|null $fecha_caducidad
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'tenant_id',
        'producto_id',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_caducidad',
    ];

    protected function casts(): array
    {
        return [
            'fecha_fabricacion' => 'date',
            'fecha_caducidad'   => 'date',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function unidadesInventario(): HasMany
    {
        return $this->hasMany(UnidadInventario::class, 'lote_id');
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class, 'lote_id');
    }
}

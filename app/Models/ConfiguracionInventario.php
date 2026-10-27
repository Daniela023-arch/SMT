<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int   $id
 * @property int   $tenant_id
 * @property bool  $permitir_stock_negativo
 * @property bool  $controlar_lotes
 * @property bool  $controlar_caducidad
 * @property bool  $controlar_series
 * @property array|null $opciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ConfiguracionInventario extends Model
{
    protected $table = 'configuracion_inventario';

    protected $fillable = [
        'tenant_id',
        'permitir_stock_negativo',
        'controlar_lotes',
        'controlar_caducidad',
        'controlar_series',
        'opciones',
    ];

    protected function casts(): array
    {
        return [
            'permitir_stock_negativo' => 'boolean',
            'controlar_lotes'         => 'boolean',
            'controlar_caducidad'     => 'boolean',
            'controlar_series'        => 'boolean',
            'opciones'                => 'array',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}

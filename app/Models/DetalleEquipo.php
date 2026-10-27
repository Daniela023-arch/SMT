<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleEquipo extends Model
{
    protected $table = 'detalles_equipos';

    protected $fillable = [
        'tenant_id',
        'unidad_inventario_id',
        'marca',
        'modelo',
        'procesador',
        'ram',
        'almacenamiento',
        'fecha_compra',
        'fin_garantia',
        'especificaciones'
    ];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
            'fin_garantia' => 'date',
            'especificaciones' => 'array'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function unidad_inventario(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id');
    }
}

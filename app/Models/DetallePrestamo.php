<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetallePrestamo extends Model
{
    protected $table = 'detalles_prestamos';

    protected $fillable = [
        'tenant_id',
        'prestamo_id',
        'unidad_inventario_id',
        'devuelto_at',
        'estado'
    ];

    protected function casts(): array
    {
        return [
            'devuelto_at' => 'datetime'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }
    public function unidad_inventario(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id');
    }
}

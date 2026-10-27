<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mantenimiento extends Model
{
    protected $table = 'mantenimientos';

    protected $fillable = [
        'tenant_id',
        'unidad_inventario_id',
        'usuario_id',
        'tipo',
        'estado',
        'descripcion',
        'fecha_programada',
        'fecha_realizada',
        'costo'
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date',
            'fecha_realizada' => 'datetime',
            'costo' => 'decimal:2'
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
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

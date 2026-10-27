<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'tenant_id',
        'usuario_id',
        'folio',
        'estado',
        'subtotal',
        'impuestos',
        'descuento',
        'total'
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}

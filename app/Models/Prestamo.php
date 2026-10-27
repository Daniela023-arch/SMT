<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    protected $table = 'prestamos';

    protected $fillable = [
        'tenant_id',
        'usuario_id',
        'folio',
        'prestatario_nombre',
        'prestatario_contacto',
        'fecha_prestamo',
        'fecha_limite',
        'estado'
    ];

    protected function casts(): array
    {
        return [
            'fecha_prestamo' => 'date',
            'fecha_limite' => 'date'
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
    public function detallesPrestamos(): HasMany
    {
        return $this->hasMany(DetallePrestamo::class, 'prestamo_id');
    }
}

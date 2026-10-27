<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Baja extends Model
{
    protected $table = 'bajas';

    protected $fillable = [
        'tenant_id',
        'usuario_id',
        'folio',
        'motivo',
        'estado',
        'aprobada_at'
    ];

    protected function casts(): array
    {
        return [
            'aprobada_at' => 'datetime'
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
    public function detallesBaja(): HasMany
    {
        return $this->hasMany(DetalleBaja::class, 'baja_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    protected $table = 'almacenes';

    protected $fillable = [
        'tenant_id',
        'establecimiento_id',
        'codigo',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class);
    }
}

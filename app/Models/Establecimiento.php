<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Establecimiento extends Model
{
    use SoftDeletes;

    protected $table = 'establecimientos';

    protected $fillable = [
        'tenant_id',
        'codigo',
        'nombre',
        'tipo',
        'email',
        'telefono',
        'direccion',
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

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_establecimiento', 'establecimiento_id', 'usuario_id')
            ->withPivot(['tenant_id', 'asignado_por'])
            ->withTimestamps();
    }
}

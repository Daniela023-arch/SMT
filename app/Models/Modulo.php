<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int    $id
 * @property string $clave
 * @property string $nombre
 * @property bool   $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'clave',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'modulos_tenant', 'modulo_id', 'tenant_id')
                    ->withPivot('habilitado')
                    ->withTimestamps();
    }
}

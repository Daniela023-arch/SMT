<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int|null    $ubicacion_padre_id
 * @property string      $nombre
 * @property string|null $codigo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'tenant_id',
        'ubicacion_padre_id',
        'nombre',
        'codigo',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function ubicacionPadre(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_padre_id');
    }

    public function sububicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'ubicacion_padre_id');
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class, 'ubicacion_id');
    }

    public function unidadesInventario(): HasMany
    {
        return $this->hasMany(UnidadInventario::class, 'ubicacion_id');
    }
}

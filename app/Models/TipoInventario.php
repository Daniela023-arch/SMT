<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $nombre
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TipoInventario extends Model
{
    protected $table = 'tipos_inventario';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'tipo_inventario_id');
    }

    public function solicitudesInventario(): HasMany
    {
        return $this->hasMany(SolicitudInventario::class, 'tipo_inventario_id');
    }
}

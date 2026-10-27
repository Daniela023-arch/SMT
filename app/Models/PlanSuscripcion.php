<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $nombre
 * @property float       $precio
 * @property string      $moneda
 * @property string      $periodicidad
 * @property int|null    $limite_usuarios
 * @property int|null    $limite_productos
 * @property bool        $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PlanSuscripcion extends Model
{
    protected $table = 'planes_suscripcion';

    protected $fillable = [
        'nombre',
        'precio',
        'moneda',
        'periodicidad',
        'limite_usuarios',
        'limite_productos',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio'           => 'decimal:2',
            'activo'           => 'boolean',
            'limite_usuarios'  => 'integer',
            'limite_productos' => 'integer',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function suscripciones(): HasMany
    {
        return $this->hasMany(SuscripcionTenant::class, 'plan_id');
    }
}

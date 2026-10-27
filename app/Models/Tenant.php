<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string      $nombre
 * @property string      $slug
 * @property string|null $email
 * @property string      $estado
 * @property int|null    $tipo_inventario_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'nombre',
        'slug',
        'email',
        'estado',
        'tipo_inventario_id',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tipoInventario(): BelongsTo
    {
        return $this->belongsTo(TipoInventario::class, 'tipo_inventario_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Rol::class, 'tenant_id');
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(ModuloTenant::class, 'tenant_id');
    }

    public function configuracionInventario(): HasOne
    {
        return $this->hasOne(ConfiguracionInventario::class, 'tenant_id');
    }

    public function solicitudesInventario(): HasMany
    {
        return $this->hasMany(SolicitudInventario::class, 'tenant_id');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'tenant_id');
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'tenant_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'tenant_id');
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'tenant_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'tenant_id');
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class, 'tenant_id');
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(SuscripcionTenant::class, 'tenant_id');
    }

    public function idempotencias(): HasMany
    {
        return $this->hasMany(Idempotencia::class, 'tenant_id');
    }

    public function importacionesCsv(): HasMany
    {
        return $this->hasMany(ImportacionCsv::class, 'tenant_id');
    }
}

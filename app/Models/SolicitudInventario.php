<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int|null    $tenant_id
 * @property int|null    $usuario_solicitante_id
 * @property int         $tipo_inventario_id
 * @property string      $nombre_negocio
 * @property string      $email_contacto
 * @property string      $estado
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SolicitudInventario extends Model
{
    protected $table = 'solicitudes_inventario';

    protected $fillable = [
        'tenant_id',
        'usuario_solicitante_id',
        'tipo_inventario_id',
        'nombre_negocio',
        'email_contacto',
        'estado',
        'observaciones',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_solicitante_id');
    }

    public function tipoInventario(): BelongsTo
    {
        return $this->belongsTo(TipoInventario::class, 'tipo_inventario_id');
    }
}

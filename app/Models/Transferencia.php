<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transferencia extends Model
{
    public const ESTADOS = [
        'solicitada', 'aprobada', 'rechazada', 'en_transito',
        'recibida_parcial', 'completada', 'cancelada',
    ];

    protected $table = 'transferencias';

    protected $fillable = [
        'tenant_id',
        'establecimiento_origen_id',
        'establecimiento_destino_id',
        'almacen_origen_id',
        'almacen_destino_id',
        'solicitante_id',
        'autorizador_id',
        'folio',
        'estado',
        'motivo',
        'motivo_rechazo',
        'solicitada_at',
        'autorizada_at',
        'en_transito_at',
        'recibida_at',
        'cancelada_at',
    ];

    protected function casts(): array
    {
        return [
            'solicitada_at' => 'datetime',
            'autorizada_at' => 'datetime',
            'en_transito_at' => 'datetime',
            'recibida_at' => 'datetime',
            'cancelada_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizador_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransferenciaDetalle::class);
    }
}

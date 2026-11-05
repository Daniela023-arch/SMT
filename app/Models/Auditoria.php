<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    public $timestamps = false;

    protected $table = 'auditoria';

    protected $fillable = [
        'tenant_id',
        'usuario_id',
        'establecimiento_id',
        'accion',
        'entidad',
        'entidad_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'agente_usuario',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

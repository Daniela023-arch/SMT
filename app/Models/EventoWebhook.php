<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoWebhook extends Model
{
    protected $table = 'eventos_webhook';

    protected $fillable = [
        'proveedor',
        'evento_externo_id',
        'tipo',
        'payload',
        'estado',
        'intentos',
        'procesado_at',
        'ultimo_error'
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'intentos' => 'integer',
            'procesado_at' => 'datetime'
        ];
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Idempotencia extends Model
{
    protected $table = 'idempotencias';

    protected $fillable = [
        'tenant_id',
        'clave',
        'operacion',
        'estado',
        'respuesta',
        'expira_at'
    ];

    protected function casts(): array
    {
        return [
            'respuesta' => 'array',
            'expira_at' => 'datetime'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}

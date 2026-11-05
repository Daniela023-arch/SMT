<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionTenant extends Model
{
    protected $table = 'configuraciones_tenant';

    protected $fillable = [
        'tenant_id',
        'logotipo',
        'color_primario',
        'color_secundario',
        'preferencias',
    ];

    protected function casts(): array
    {
        return ['preferencias' => 'array'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

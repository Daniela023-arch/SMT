<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportacionCsv extends Model
{
    protected $table = 'importaciones_csv';

    protected $fillable = [
        'tenant_id',
        'usuario_id',
        'archivo',
        'estado',
        'filas_totales',
        'filas_exitosas',
        'filas_fallidas',
        'errores'
    ];

    protected function casts(): array
    {
        return [
            'filas_totales' => 'integer',
            'filas_exitosas' => 'integer',
            'filas_fallidas' => 'integer',
            'errores' => 'array'
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

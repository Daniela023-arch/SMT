<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int         $producto_id
 * @property string|null $isbn
 * @property string|null $autor
 * @property string|null $editorial
 * @property int|null    $anio_publicacion
 * @property string|null $edicion
 * @property string|null $idioma
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DatosBibliograficos extends Model
{
    protected $table = 'datos_bibliograficos';

    protected $fillable = [
        'tenant_id',
        'producto_id',
        'isbn',
        'autor',
        'editorial',
        'anio_publicacion',
        'edicion',
        'idioma',
    ];

    protected function casts(): array
    {
        return [
            'anio_publicacion' => 'integer',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}

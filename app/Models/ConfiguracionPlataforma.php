<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPlataforma extends Model
{
    protected $table = 'configuraciones_plataforma';

    protected $fillable = ['clave', 'valor', 'actualizado_por'];
}

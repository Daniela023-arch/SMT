<?php

namespace Database\Seeders;

use App\Models\ConfiguracionPlataforma;
use Illuminate\Database\Seeder;

class ConfiguracionPlataformaSeeder extends Seeder
{
    /** @var array<string, string> */
    private const CONFIGURACION = [
        'nombre_comercial' => 'MULTIVERSE',
        'correo_soporte' => '',
        'zona_horaria' => 'America/Mexico_City',
    ];

    public function run(): void
    {
        foreach (self::CONFIGURACION as $clave => $valor) {
            ConfiguracionPlataforma::query()->firstOrCreate(
                ['clave' => $clave],
                ['valor' => $valor],
            );
        }
    }
}

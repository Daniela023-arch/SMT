<?php

namespace App\Livewire\Superadministrador;

use App\Models\ConfiguracionPlataforma;
use App\Services\AuditoriaService;
use Flux\Flux;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Configuración global | MULTIVERSE')]
class Configuracion extends Component
{
    public string $nombreComercial = '';
    public string $correoSoporte = '';
    public string $zonaHoraria = 'America/Mexico_City';

    public function mount(): void
    {
        $this->nombreComercial = (string) (ConfiguracionPlataforma::query()->where('clave', 'nombre_comercial')->value('valor') ?: config('app.name', 'MULTIVERSE'));
        $this->correoSoporte = (string) (ConfiguracionPlataforma::query()->where('clave', 'correo_soporte')->value('valor') ?: config('mail.from.address', ''));
        $this->zonaHoraria = (string) (ConfiguracionPlataforma::query()->where('clave', 'zona_horaria')->value('valor') ?: config('app.timezone', 'America/Mexico_City'));
    }

    public function guardar(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'nombreComercial' => ['required', 'string', 'min:2', 'max:120'],
            'correoSoporte' => ['required', 'email', 'max:255'],
            'zonaHoraria' => ['required', Rule::in(timezone_identifiers_list())],
        ]);

        $anterior = [
            'nombre_comercial' => Config::get('app.name'),
            'correo_soporte' => Config::get('mail.from.address'),
            'zona_horaria' => Config::get('app.timezone'),
        ];
        $nuevos = [
            'nombre_comercial' => $datos['nombreComercial'],
            'correo_soporte' => $datos['correoSoporte'],
            'zona_horaria' => $datos['zonaHoraria'],
        ];

        foreach ($nuevos as $clave => $valor) {
            ConfiguracionPlataforma::query()->updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor, 'actualizado_por' => auth()->id()],
            );
        }

        Config::set([
            'app.name' => $datos['nombreComercial'],
            'mail.from.address' => $datos['correoSoporte'],
            'app.timezone' => $datos['zonaHoraria'],
        ]);
        date_default_timezone_set($datos['zonaHoraria']);
        $auditoria->registrar((int) auth()->id(), 'configuracion_plataforma_actualizada', 'ConfiguracionPlataforma', null, null, $anterior, $nuevos);
        Flux::toast(variant: 'success', text: 'Configuración global guardada.');
    }

    public function render()
    {
        return view('livewire.superadministrador.configuracion')->layout('layouts.superadministrador');
    }
}

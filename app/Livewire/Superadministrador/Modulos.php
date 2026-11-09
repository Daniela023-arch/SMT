<?php

namespace App\Livewire\Superadministrador;

use App\Models\Modulo;
use App\Models\ModuloTenant;
use App\Models\Tenant;
use App\Services\AuditoriaService;
use App\Services\ModuloTenantService;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Módulos | MULTIVERSE')]
class Modulos extends Component
{
    public ?int $tenantId = null;
    public string $nombre = '';
    public string $clave = '';

    public function mount(): void
    {
        $this->tenantId = Tenant::query()->where('estado', 'activo')->orderBy('nombre')->value('id');
    }

    public function updatedNombre(string $nombre): void
    {
        if ($this->clave === '' || $this->clave === Str::slug($nombre, '_')) {
            $this->clave = Str::slug($nombre, '_');
        }
    }

    public function crearModulo(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'clave' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('modulos', 'clave')],
        ]);

        $modulo = Modulo::query()->create(['nombre' => $datos['nombre'], 'clave' => $datos['clave'], 'activo' => true]);
        $auditoria->registrar((int) auth()->id(), 'modulo_global_creado', 'Modulo', $modulo->id, null, null, ['clave' => $modulo->clave, 'nombre' => $modulo->nombre]);
        $this->reset('nombre', 'clave');
        Flux::toast(variant: 'success', text: 'Módulo agregado al catálogo global.');
    }

    public function cambiarActivoGlobal(int $moduloId, AuditoriaService $auditoria): void
    {
        $modulo = Modulo::query()->findOrFail($moduloId);
        $anterior = $modulo->activo;
        $modulo->update(['activo' => ! $modulo->activo]);
        $auditoria->registrar((int) auth()->id(), 'modulo_estado_global', 'Modulo', $modulo->id, null, ['activo' => $anterior], ['activo' => $modulo->activo]);
        Flux::toast(variant: 'success', text: $modulo->activo ? 'Módulo habilitado en el catálogo.' : 'Módulo desactivado globalmente.');
    }

    public function cambiarTenant(int $moduloId, bool $habilitado, ModuloTenantService $service, AuditoriaService $auditoria): void
    {
        $tenantId = (int) $this->validate(['tenantId' => ['required', 'integer', 'exists:tenants,id']])['tenantId'];
        $modulo = Modulo::query()->where('activo', true)->findOrFail($moduloId);
        $antes = ModuloTenant::query()->where('tenant_id', $tenantId)->where('modulo_id', $moduloId)->value('habilitado');
        $service->habilitar($tenantId, $modulo->clave, $habilitado);
        $auditoria->registrar((int) auth()->id(), 'modulo_tenant_actualizado', 'ModuloTenant', $moduloId, $tenantId, ['habilitado' => $antes], ['habilitado' => $habilitado]);
        Flux::toast(variant: 'success', text: $habilitado ? 'Módulo autorizado para la empresa.' : 'Autorización retirada.');
    }

    public function render()
    {
        $modulos = Modulo::query()->withCount('tenants')->orderBy('nombre')->get();
        $tenants = Tenant::query()->where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $autorizados = $this->tenantId
            ? ModuloTenant::query()->where('tenant_id', $this->tenantId)->where('habilitado', true)->pluck('modulo_id')->all()
            : [];

        return view('livewire.superadministrador.modulos', compact('modulos', 'tenants', 'autorizados'))->layout('layouts.superadministrador');
    }
}

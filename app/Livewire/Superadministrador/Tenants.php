<?php

namespace App\Livewire\Superadministrador;

use App\Models\Tenant;
use App\Services\AuditoriaService;
use App\Services\TenantService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Tenants | MULTIVERSE')]
class Tenants extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $estadoFiltro = 'todos';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function cambiarEstado(int $tenantId, string $estado, TenantService $service, AuditoriaService $auditoria): void
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $anterior = $tenant->estado;
        $service->cambiarEstado($tenantId, $estado);
        $auditoria->registrar((int) auth()->id(), 'tenant_estado_actualizado', 'Tenant', $tenantId, $tenantId, ['estado' => $anterior], ['estado' => $estado]);
        Flux::toast(variant: 'success', text: 'Estado del tenant actualizado.');
    }

    public function render()
    {
        $tenants = Tenant::query()
            ->with(['tipoInventario:id,nombre', 'suscripciones.plan'])
            ->withCount(['usuarios', 'establecimientos', 'productos'])
            ->when($this->estadoFiltro !== 'todos', fn ($query) => $query->where('estado', $this->estadoFiltro))
            ->when($this->buscar !== '', fn ($query) => $query->where(fn ($tenants) => $tenants
                ->where('nombre', 'like', '%'.$this->buscar.'%')
                ->orWhere('slug', 'like', '%'.$this->buscar.'%')
                ->orWhere('email', 'like', '%'.$this->buscar.'%')))
            ->latest('id')
            ->paginate(15);

        return view('livewire.superadministrador.tenants', compact('tenants'))->layout('layouts.superadministrador');
    }
}

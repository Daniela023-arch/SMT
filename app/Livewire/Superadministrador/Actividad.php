<?php

namespace App\Livewire\Superadministrador;

use App\Models\Auditoria;
use App\Models\StockMovimiento;
use App\Models\Tenant;
use App\Models\Transferencia;
use App\Models\User;
use App\Models\Venta;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Actividad global | MULTIVERSE')]
class Actividad extends Component
{
    use WithPagination;

    public string $tenantId = '';
    public string $desde = '';
    public string $hasta = '';
    public string $buscarUsuario = '';

    public function mount(): void
    {
        $this->desde = now()->subDays(30)->toDateString();
        $this->hasta = today()->toDateString();
    }

    public function updated(): void
    {
        $this->resetPage();
        $this->resetPage(pageName: 'movimientosPage');
        $this->resetPage(pageName: 'ventasPage');
        $this->resetPage(pageName: 'transferenciasPage');
        $this->resetPage(pageName: 'usuariosPage');
    }

    private function aplicarFiltros($query, string $columnaFecha = 'created_at')
    {
        if ($this->tenantId !== '') $query->where('tenant_id', (int) $this->tenantId);
        if ($this->fechaValida($this->desde)) $query->whereDate($columnaFecha, '>=', $this->desde);
        if ($this->fechaValida($this->hasta)) $query->whereDate($columnaFecha, '<=', $this->hasta);

        return $query;
    }

    private function fechaValida(string $fecha): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1 && strtotime($fecha) !== false;
    }

    public function render()
    {
        $usuarios = User::query()->whereNotNull('tenant_id')
            ->when($this->tenantId !== '', fn ($query) => $query->where('tenant_id', (int) $this->tenantId))
            ->when(trim($this->buscarUsuario) !== '', fn ($query) => $query->where(function ($users): void {
                $term = '%'.trim($this->buscarUsuario).'%';
                $users->where('name', 'ilike', $term)->orWhere('email', 'ilike', $term);
            }))
            ->with(['tenant:id,nombre', 'roles', 'establecimientos:id,nombre', 'almacenesAsignados:id,nombre'])
            ->orderBy('tenant_id')->orderBy('name')
            ->paginate(15, pageName: 'usuariosPage');

        return view('livewire.superadministrador.actividad', [
            'tenants' => Tenant::query()->orderBy('nombre')->get(['id', 'nombre']),
            'usuarios' => $usuarios,
            'auditorias' => $this->aplicarFiltros(Auditoria::query()->with(['tenant:id,nombre', 'usuario:id,name'])->latest('created_at'))->paginate(15),
            'movimientos' => $this->aplicarFiltros(StockMovimiento::query()->with(['tenant:id,nombre', 'usuario:id,name', 'producto:id,nombre,sku', 'almacenOrigen:id,nombre', 'almacenDestino:id,nombre'])->latest(), 'stock_movimientos.created_at')->paginate(15, pageName: 'movimientosPage'),
            'ventas' => $this->aplicarFiltros(Venta::query()->with(['tenant:id,nombre', 'usuario:id,name', 'establecimiento:id,nombre', 'almacen:id,nombre'])->latest(), 'ventas.created_at')->paginate(15, pageName: 'ventasPage'),
            'transferencias' => $this->aplicarFiltros(Transferencia::query()->with(['tenant:id,nombre', 'solicitante:id,name', 'almacenOrigen:id,nombre', 'almacenDestino:id,nombre'])->latest(), 'transferencias.created_at')->paginate(15, pageName: 'transferenciasPage'),
        ])->layout('layouts.superadministrador');
    }
}

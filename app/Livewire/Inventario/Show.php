<?php

namespace App\Livewire\Inventario;

use App\Models\Almacen;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Services\InventarioService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Inventario por almacén | MULTIVERSE')]
class Show extends Component
{
    use WithPagination;

    #[Locked]
    public int $establecimiento;

    #[Locked]
    public int $almacen;

    public int $productoId = 0;

    public string $cantidad = '';

    public string $motivo = '';

    public string $buscar = '';

    public function mount(int $establecimiento, int $almacen): void
    {
        $this->establecimiento = $establecimiento;
        $this->almacen = $almacen;
    }

    public function registrarEntrada(InventarioService $service): void
    {
        $this->autorizarOperador();
        $datos = $this->validate([
            'productoId' => ['required', 'integer', 'min:1'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->registrarEntradaEnAlmacen(
            (int) auth()->user()->tenant_id,
            $datos['productoId'],
            $this->almacen,
            (float) $datos['cantidad'],
            usuarioId: (int) auth()->id(),
            motivo: $datos['motivo'] ?: 'Entrada registrada desde almacén',
            tipo: 'entrada_manual',
        );

        $this->reset('cantidad', 'motivo');
        Flux::toast(variant: 'success', text: 'Entrada registrada.');
    }

    public function registrarSalida(InventarioService $service): void
    {
        $this->autorizarOperador();
        $datos = $this->validate([
            'productoId' => ['required', 'integer', 'min:1'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'motivo' => ['required', 'string', 'max:1000'],
        ]);

        $service->registrarSalidaDeAlmacen(
            (int) auth()->user()->tenant_id,
            $datos['productoId'],
            $this->almacen,
            (float) $datos['cantidad'],
            usuarioId: (int) auth()->id(),
            motivo: $datos['motivo'],
            tipo: 'salida_manual',
        );

        $this->reset('cantidad', 'motivo');
        Flux::toast(variant: 'success', text: 'Salida registrada.');
    }

    private function autorizarOperador(): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $hasRole = $user->roles()
            ->whereIn('roles.slug', ['admin', 'almacen'])
            ->wherePivot('tenant_id', $tenantId)
            ->exists();
        $warehouse = Almacen::query()->where('tenant_id', $tenantId)->where('establecimiento_id', $this->establecimiento)
            ->where('activo', true)->whereHas('establecimiento', fn ($query) => $query->where('activo', true))->find($this->almacen);
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())
            ->where('establecimiento_id', $this->establecimiento)->exists();

        abort_unless($hasRole && $warehouse !== null && ($admin || $assigned), 403);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $existencias = Existencia::query()
            ->where('tenant_id', $tenantId)
            ->where('almacen_id', $this->almacen)
            ->whereHas('producto', fn ($query) => $query->where('nombre', 'like', '%'.$this->buscar.'%')->orWhere('sku', 'like', '%'.$this->buscar.'%'))
            ->with(['producto:id,sku,nombre,unidad_medida,stock_minimo', 'ubicacion:id,nombre,codigo', 'lote:id,numero_lote'])
            ->orderBy('producto_id')
            ->paginate(30);
        $movimientos = StockMovimiento::query()
            ->where('tenant_id', $tenantId)
            ->where(fn ($query) => $query->where('almacen_origen_id', $this->almacen)->orWhere('almacen_destino_id', $this->almacen))
            ->with('producto:id,sku,nombre')
            ->latest('id')
            ->limit(20)
            ->get();
        $productos = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->orderBy('nombre')->get(['id', 'sku', 'nombre']);

        return view('livewire.inventario.show', compact('existencias', 'movimientos', 'productos'))->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\Inventario;

use App\Models\Almacen;
use App\Models\CodigoIdentificacion;
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

    public string $codigoEscaneado = '';

    public string $tipoCodigo = 'barcode';

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

    public function buscarCodigo(): void
    {
        $this->autorizarOperador();
        $datos = $this->validate(['codigoEscaneado' => ['required', 'string', 'max:255']]);
        $tenantId = (int) auth()->user()->tenant_id;
        $codigo = trim($datos['codigoEscaneado']);
        $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)
            ->where(fn ($query) => $query->where('sku', $codigo)
                ->orWhereHas('codigosIdentificacion', fn ($codes) => $codes->where('tenant_id', $tenantId)->where('codigo', $codigo)))
            ->first();

        if ($producto === null) {
            $this->addError('codigoEscaneado', 'No encontramos ese código. Selecciona el producto y guarda el código para asociarlo.');
            return;
        }

        $this->productoId = (int) $producto->getKey();
        $this->resetErrorBag('codigoEscaneado');
        Flux::toast(variant: 'success', text: "Producto seleccionado: {$producto->nombre}.");
    }

    public function guardarCodigo(): void
    {
        $this->autorizarOperador();
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'productoId' => ['required', 'integer', 'min:1'],
            'codigoEscaneado' => ['required', 'string', 'max:255'],
            'tipoCodigo' => ['required', 'in:barcode,qr'],
        ]);
        $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->findOrFail($datos['productoId']);
        $codigo = trim($datos['codigoEscaneado']);

        if (CodigoIdentificacion::query()->where('tenant_id', $tenantId)->where('codigo', $codigo)->exists()) {
            $this->addError('codigoEscaneado', 'Ese código ya está asignado a un producto.');
            return;
        }

        CodigoIdentificacion::query()->create([
            'tenant_id' => $tenantId,
            'producto_id' => $producto->getKey(),
            'tipo' => $datos['tipoCodigo'],
            'codigo' => $codigo,
        ]);

        $this->reset('codigoEscaneado');
        Flux::toast(variant: 'success', text: 'Código asociado al producto. Ya puedes usarlo para escanear entradas.');
    }

    private function autorizarOperador(): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $hasRole = $user->roles()
            ->whereIn('roles.slug', ['admin', 'gerente', 'almacen'])
            ->wherePivot('tenant_id', $tenantId)
            ->exists();
        $warehouse = Almacen::query()->where('tenant_id', $tenantId)->where('establecimiento_id', $this->establecimiento)
            ->where('activo', true)->whereHas('establecimiento', fn ($query) => $query->where('activo', true))->find($this->almacen);
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $manager = $user->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists();
        $isWarehouseOperator = $user->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())
            ->where('establecimiento_id', $this->establecimiento)->exists();
        $warehouseAssigned = DB::table('usuario_almacen')->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())->where('almacen_id', $this->almacen)->exists();

        abort_unless($hasRole && $warehouse !== null && (! $manager || $user->puede_ver_almacen) && ($admin || ($isWarehouseOperator ? $warehouseAssigned : $assigned)), 403);
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

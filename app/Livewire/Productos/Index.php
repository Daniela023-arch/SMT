<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use App\Services\ProductoService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Catálogo | MULTIVERSE')]
class Index extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $sku = '';

    public string $nombre = '';

    public string $unidadMedida = 'pieza';

    public string $precio = '0';

    public string $stockMinimo = '0';

    public ?int $categoriaId = null;

    public function crear(ProductoService $service): void
    {
        abort_unless(auth()->user()->roles()
            ->where('roles.slug', 'admin')
            ->wherePivot('tenant_id', auth()->user()->tenant_id)
            ->exists(), 403);

        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('productos', 'sku')->where('tenant_id', $tenantId)],
            'nombre' => ['required', 'string', 'max:255'],
            'unidadMedida' => ['required', 'string', 'max:30'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stockMinimo' => ['required', 'numeric', 'min:0'],
            'categoriaId' => ['nullable', 'integer'],
        ]);

        $service->crear($tenantId, [
            'sku' => $datos['sku'],
            'nombre' => $datos['nombre'],
            'unidad_medida' => $datos['unidadMedida'],
            'precio_venta' => $datos['precio'],
            'stock_minimo' => $datos['stockMinimo'],
            'categoria_id' => $datos['categoriaId'],
        ]);

        $this->reset('sku', 'nombre', 'precio', 'stockMinimo', 'categoriaId');
        Flux::toast(variant: 'success', text: 'Producto agregado al catálogo empresarial.');
    }

    public function render()
    {
        $productos = Producto::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('activo', true)
            ->when($this->buscar !== '', fn ($query) => $query->where(fn ($items) => $items
                ->where('nombre', 'like', '%'.$this->buscar.'%')
                ->orWhere('sku', 'like', '%'.$this->buscar.'%')))
            ->with('categoria:id,nombre')
            ->orderBy('nombre')
            ->paginate(25);

        return view('livewire.productos.index', compact('productos'))->layout('layouts.app');
    }
}

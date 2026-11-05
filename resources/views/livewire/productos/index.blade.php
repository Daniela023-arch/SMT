<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div><flux:heading size="xl">Catálogo de productos</flux:heading><flux:subheading>Catálogo central y precios generales de tu empresa.</flux:subheading></div>

    @if(auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <form wire:submit="crear" class="grid gap-4 rounded-xl border border-zinc-200 p-5 sm:grid-cols-2 lg:grid-cols-3 dark:border-zinc-700">
            <flux:input wire:model="sku" label="SKU" required />
            <flux:input wire:model="nombre" label="Producto" required />
            <flux:input wire:model="unidadMedida" label="Unidad de medida" required />
            <flux:input wire:model="precio" type="number" min="0" step="0.01" label="Precio general" required />
            <flux:input wire:model="stockMinimo" type="number" min="0" step="0.001" label="Stock mínimo" required />
            <div class="flex items-end"><flux:button type="submit" variant="primary">Agregar producto</flux:button></div>
        </form>
    @endif

    <div class="flex max-w-md gap-3"><flux:input wire:model.live.debounce.300ms="buscar" label="Buscar producto" placeholder="Nombre o SKU" /></div>
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800"><tr><th class="p-3">SKU</th><th class="p-3">Producto</th><th class="p-3">Categoría</th><th class="p-3">Unidad</th><th class="p-3 text-right">Precio</th><th class="p-3 text-right">Mínimo</th></tr></thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="p-3">{{ $producto->sku }}</td><td class="p-3 font-medium">{{ $producto->nombre }}</td><td class="p-3">{{ $producto->categoria?->nombre ?? '—' }}</td><td class="p-3">{{ $producto->unidad_medida }}</td><td class="p-3 text-right">{{ number_format((float) $producto->precio_venta, 2) }}</td><td class="p-3 text-right">{{ $producto->stock_minimo }}</td></tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-zinc-500">No hay productos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $productos->links() }}
</div>

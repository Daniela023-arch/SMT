<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div><flux:heading size="xl">Inventario del almacén</flux:heading><flux:subheading>Consulta existencias y registra operaciones físicas autorizadas.</flux:subheading></div>

    @if(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'almacen'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <section class="grid gap-4 md:grid-cols-2">
            <form wire:submit="registrarEntrada" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Registrar entrada</flux:heading>
                <flux:select wire:model="productoId" label="Producto" required><flux:select.option value="0">Selecciona</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="cantidad" type="number" min="0.0001" step="0.0001" label="Cantidad" required />
                <flux:input wire:model="motivo" label="Motivo (opcional)" />
                <flux:button type="submit" variant="primary">Guardar entrada</flux:button>
            </form>
            <form wire:submit="registrarSalida" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Registrar salida</flux:heading>
                <flux:select wire:model="productoId" label="Producto" required><flux:select.option value="0">Selecciona</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="cantidad" type="number" min="0.0001" step="0.0001" label="Cantidad" required />
                <flux:input wire:model="motivo" label="Motivo" required />
                <flux:button type="submit" variant="primary">Guardar salida</flux:button>
            </form>
        </section>
    @endif

    <section class="space-y-4">
        <flux:input wire:model.live.debounce.300ms="buscar" label="Buscar existencias" placeholder="Producto" />
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-left text-sm"><thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800"><tr><th class="p-3">SKU</th><th class="p-3">Producto</th><th class="p-3">Ubicación</th><th class="p-3">Lote</th><th class="p-3 text-right">Existencia</th></tr></thead><tbody>
                @forelse($existencias as $existencia)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="p-3">{{ $existencia->producto->sku }}</td><td class="p-3">{{ $existencia->producto->nombre }}</td><td class="p-3">{{ $existencia->ubicacion?->nombre ?? '—' }}</td><td class="p-3">{{ $existencia->lote?->numero_lote ?? '—' }}</td><td class="p-3 text-right">{{ $existencia->cantidad }} {{ $existencia->producto->unidad_medida }}</td></tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-zinc-500">Sin existencias registradas.</td></tr>
                @endforelse
            </tbody></table>
        </div>{{ $existencias->links() }}
    </section>

    <section class="space-y-3"><flux:heading size="lg">Movimientos recientes</flux:heading>
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700"><table class="w-full text-left text-sm"><thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800"><tr><th class="p-3">Fecha</th><th class="p-3">Tipo</th><th class="p-3">Producto</th><th class="p-3 text-right">Cantidad</th></tr></thead><tbody>
            @forelse($movimientos as $movimiento)
                <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="p-3">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td><td class="p-3">{{ str_replace('_', ' ', $movimiento->tipo) }}</td><td class="p-3">{{ $movimiento->producto?->nombre }}</td><td class="p-3 text-right">{{ $movimiento->cantidad }}</td></tr>
            @empty
                <tr><td colspan="4" class="p-8 text-center text-zinc-500">Sin movimientos.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>

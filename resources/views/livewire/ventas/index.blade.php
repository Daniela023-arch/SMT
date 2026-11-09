<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div><flux:heading size="xl">Ventas</flux:heading><flux:subheading>Registra ventas en borrador, confirma el descuento de inventario y agrega pagos.</flux:subheading></div>

    <form wire:submit="registrarVenta" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        <div class="flex items-center justify-between"><flux:heading size="lg">Nueva venta</flux:heading><flux:button type="button" wire:click="agregarLinea">Agregar producto</flux:button></div>
        @if($errors->any())
            <div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                <p class="font-semibold">No se pudo completar la operación:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @foreach($lineas as $indice => $linea)
            <div wire:key="linea-{{ $indice }}" class="grid gap-3 rounded-lg bg-zinc-50 p-4 sm:grid-cols-4 dark:bg-zinc-800">
                <flux:select wire:model="lineas.{{ $indice }}.producto_id" label="Producto" required>
                    <flux:select.option value="">Selecciona</flux:select.option>
                    @foreach($productos as $producto)
                        <flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }} · ${{ number_format((float) $producto->precio_venta, 2) }} · Disponible: {{ number_format((float) ($existenciasPorProducto[$producto->id] ?? 0), 3) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="lineas.{{ $indice }}.cantidad" type="number" min="0.001" step="0.001" label="Cantidad" required />
                <flux:input wire:model="lineas.{{ $indice }}.lote_id" type="number" min="1" label="Lote (opcional)" />
                <div class="flex items-end gap-2"><flux:input wire:model="lineas.{{ $indice }}.unidad_inventario_id" type="number" min="1" label="Serie / unidad (si aplica)" />@if(count($lineas) > 1)<flux:button type="button" wire:click="quitarLinea({{ $indice }})" variant="danger">Quitar</flux:button>@endif</div>
                @php($productoSeleccionado = $productos->firstWhere('id', (int) ($linea['producto_id'] ?? 0)))
                @if($productoSeleccionado)
                    <p class="text-xs text-zinc-600 sm:col-span-4 dark:text-zinc-300">
                        En este almacén hay <strong>{{ number_format((float) ($existenciasPorProducto[$productoSeleccionado->id] ?? 0), 3) }} {{ $productoSeleccionado->unidad_medida ?? 'unidades' }}</strong> disponibles.
                        Importe estimado de la línea: <strong>${{ number_format((float) $productoSeleccionado->precio_venta * (float) ($linea['cantidad'] ?: 0), 2) }}</strong>.
                        La venta solo descontará existencias al confirmarla.
                    </p>
                @endif
            </div>
        @endforeach
        <flux:button type="submit" variant="primary">Crear borrador</flux:button>
    </form>

    <section class="space-y-4">
        <flux:heading size="lg">Ventas del almacén</flux:heading>
        @forelse($ventas as $venta)
            <article class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="font-semibold">{{ $venta->folio }}</h2><p class="text-sm text-zinc-500">{{ $venta->created_at->format('d/m/Y H:i') }} · {{ $venta->usuario?->name }}</p></div>
                    <div class="text-right"><p class="font-semibold">{{ number_format((float) $venta->total, 2) }}</p><p class="text-sm">{{ ucfirst($venta->estado) }}</p></div>
                </div>
                <ul class="space-y-1 text-sm">@foreach($venta->detallesVenta as $detalle)<li>{{ $detalle->cantidad }} × {{ $detalle->producto?->nombre }} — {{ number_format((float) $detalle->total, 2) }}</li>@endforeach</ul>
                @if($venta->estado === 'borrador')
                    <flux:button wire:click="confirmar({{ $venta->id }})" variant="primary">Confirmar y descontar stock</flux:button>
                @elseif($venta->estado === 'confirmada')
                    <div class="flex flex-wrap items-end gap-3">
                        <flux:input wire:model="montoPago" type="number" min="0.01" step="0.01" label="Monto del pago" />
                        <flux:select wire:model="metodoPago" label="Método"><flux:select.option value="efectivo">Efectivo</flux:select.option><flux:select.option value="tarjeta">Tarjeta</flux:select.option><flux:select.option value="transferencia">Transferencia</flux:select.option><flux:select.option value="otro">Otro</flux:select.option></flux:select>
                        <flux:input wire:model="referenciaPago" label="Referencia (opcional)" />
                        <flux:button wire:click="$set('ventaPagoId', {{ $venta->id }})" type="button">Seleccionar</flux:button>
                        @if($ventaPagoId === $venta->id)<flux:button wire:click="registrarPago" variant="primary">Registrar pago</flux:button>@endif
                    </div>
                    <p class="text-xs text-zinc-500">Pagado: {{ number_format((float) $venta->pagos->where('estado', 'pagado')->sum('monto'), 2) }}</p>
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-dashed p-8 text-center text-zinc-500">Aún no hay ventas.</p>
        @endforelse
        {{ $ventas->links() }}
    </section>
</div>

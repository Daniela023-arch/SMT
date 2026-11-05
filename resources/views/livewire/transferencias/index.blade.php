<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div><flux:heading size="xl">Transferencias</flux:heading><flux:subheading>Solicita, autoriza, despacha y recibe mercancía entre almacenes.</flux:subheading></div>

    @if(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'almacen'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <form wire:submit="solicitar" class="grid gap-4 rounded-xl border border-zinc-200 p-5 sm:grid-cols-2 lg:grid-cols-3 dark:border-zinc-700">
            <flux:select wire:model="almacenOrigenId" label="Almacén de origen" required><flux:select.option value="0">Selecciona</flux:select.option>@foreach($almacenes as $almacen)<flux:select.option value="{{ $almacen->id }}">{{ $almacen->establecimiento?->nombre }} · {{ $almacen->nombre }}</flux:select.option>@endforeach</flux:select>
            <flux:select wire:model="almacenDestinoId" label="Almacén de destino" required><flux:select.option value="0">Selecciona</flux:select.option>@foreach($almacenes as $almacen)<flux:select.option value="{{ $almacen->id }}">{{ $almacen->establecimiento?->nombre }} · {{ $almacen->nombre }}</flux:select.option>@endforeach</flux:select>
            <flux:input wire:model="motivo" label="Motivo" />
            <div class="flex items-end"><flux:button type="button" wire:click="agregarLinea">Agregar producto</flux:button></div>
            @foreach($lineas as $indice => $linea)
                <div wire:key="transferencia-linea-{{ $indice }}" class="flex flex-wrap items-end gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                    <flux:select wire:model="lineas.{{ $indice }}.producto_id" label="Producto" required><flux:select.option value="">Selecciona</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                    <flux:input wire:model="lineas.{{ $indice }}.cantidad" type="number" min="0.0001" step="0.0001" label="Cantidad solicitada" required />
                    @if(count($lineas) > 1)<flux:button type="button" wire:click="quitarLinea({{ $indice }})">Quitar</flux:button>@endif
                </div>
            @endforeach
            <div class="sm:col-span-2 lg:col-span-3"><flux:button type="submit" variant="primary">Solicitar transferencia</flux:button></div>
        </form>
    @endif

    <section class="space-y-4">
        @forelse($transferencias as $transferencia)
            <article class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h2 class="font-semibold">{{ $transferencia->folio }}</h2><p class="text-sm text-zinc-500">{{ $transferencia->almacenOrigen?->nombre }} → {{ $transferencia->almacenDestino?->nombre }} · {{ $transferencia->solicitante?->name }}</p></div>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm dark:bg-zinc-800">{{ str_replace('_', ' ', $transferencia->estado) }}</span>
                </div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-zinc-500"><tr><th class="py-2">Producto</th><th class="py-2 text-right">Solicitada</th><th class="py-2 text-right">Enviada</th><th class="py-2 text-right">Recibida</th><th class="py-2">Recibir ahora</th></tr></thead><tbody>
                    @foreach($transferencia->detalles as $detalle)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="py-2">{{ $detalle->producto?->nombre }}</td><td class="py-2 text-right">{{ $detalle->cantidad_solicitada }}</td><td class="py-2 text-right">{{ $detalle->cantidad_enviada }}</td><td class="py-2 text-right">{{ $detalle->cantidad_recibida }}</td><td class="py-2"><input type="number" min="0" step="0.0001" wire:model="recepciones.{{ $detalle->id }}" class="w-28 rounded-md border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600" /></td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="flex flex-wrap gap-2">
                    @if($transferencia->estado === 'solicitada' && auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
                        <flux:button wire:click="autorizar({{ $transferencia->id }})" variant="primary">Autorizar</flux:button>
                        <flux:input wire:model="motivoRechazo" label="Motivo para rechazar" />
                        <flux:button wire:click="rechazar({{ $transferencia->id }})" variant="danger">Rechazar</flux:button>
                    @endif
                    @if($transferencia->estado === 'aprobada')<flux:button wire:click="despachar({{ $transferencia->id }})" variant="primary">Registrar envío</flux:button>@endif
                    @if(in_array($transferencia->estado, ['en_transito', 'recibida_parcial'], true))<flux:button wire:click="recibir({{ $transferencia->id }})" variant="primary">Registrar recepción</flux:button>@endif
                    @if(in_array($transferencia->estado, ['solicitada', 'aprobada'], true) && auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())<flux:button wire:click="cancelar({{ $transferencia->id }})">Cancelar</flux:button>@endif
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed p-8 text-center text-zinc-500">Aún no hay transferencias.</p>
        @endforelse
        {{ $transferencias->links() }}
    </section>
</div>

<div class="space-y-7">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Control de existencias</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">Inventario del almacén</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Consulta el stock y registra entradas o salidas con trazabilidad.</p></div>
        <a wire:navigate href="{{ route('establecimientos.index') }}" class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800 sm:self-auto"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg> Sucursales</a>
    </header>

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-100"><p class="font-semibold">Revisa la información</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'almacen'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="mb-5"><h2 class="font-semibold text-slate-950 dark:text-white">Escanear producto</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Lee un código para seleccionarlo. Puedes usar cámara, lector externo o escribirlo.</p></div>
            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(260px,0.8fr)]">
                <x-barcode-scanner model="codigoEscaneado" action="buscarCodigo" />
                <div class="space-y-3">
                    <flux:select wire:model="productoId" label="Producto para asociar un código"><flux:select.option value="0">Selecciona el producto</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end"><label class="block flex-1 text-xs font-semibold text-slate-800 dark:text-slate-100">Tipo de código<select wire:model="tipoCodigo" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="barcode">Código de barras</option><option value="qr">Código QR</option></select></label><button type="button" wire:click="guardarCodigo" class="rounded-lg border border-blue-300 bg-blue-50 px-3.5 py-2.5 text-sm font-semibold text-blue-900 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-100 dark:hover:bg-blue-900/60">Asociar código</button></div>
                    <p class="text-xs leading-5 text-slate-600 dark:text-slate-300">Para un código nuevo, escanéalo y selecciónalo en el campo de arriba. Quedará ligado al producto para futuras entradas y ventas.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <form wire:submit="registrarEntrada" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div><h2 class="font-semibold text-slate-950 dark:text-white">Registrar entrada</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Recibe mercancía y súmala a este almacén.</p></div>
                <flux:select wire:model="productoId" label="Producto" required><flux:select.option value="0">Selecciona un producto</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="cantidad" type="number" min="0.0001" step="0.0001" label="Cantidad recibida" required /><flux:input wire:model="motivo" label="Referencia o motivo" placeholder="Ej. Compra a proveedor" /></div>
                <flux:button type="submit" variant="primary">Guardar entrada</flux:button>
            </form>
            <form wire:submit="registrarSalida" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div><h2 class="font-semibold text-slate-950 dark:text-white">Registrar salida</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra una baja o retiro manual de mercancía.</p></div>
                <flux:select wire:model="productoId" label="Producto" required><flux:select.option value="0">Selecciona un producto</flux:select.option>@foreach($productos as $producto)<flux:select.option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</flux:select.option>@endforeach</flux:select>
                <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="cantidad" type="number" min="0.0001" step="0.0001" label="Cantidad" required /><flux:input wire:model="motivo" label="Motivo" placeholder="Ej. Merma o ajuste" required /></div>
                <flux:button type="submit" variant="primary">Guardar salida</flux:button>
            </form>
        </section>
    @endif

    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end"><div><h2 class="text-lg font-bold text-slate-950 dark:text-white">Existencias</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">El stock registrado en las ubicaciones de este almacén.</p></div><label class="block w-full sm:max-w-xs"><span class="sr-only">Buscar existencias</span><input wire:model.live.debounce.300ms="buscar" type="search" placeholder="Buscar por producto o SKU…" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-400" /></label></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200"><tr><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Ubicación</th><th class="px-4 py-3">Lote</th><th class="px-4 py-3 text-right">Existencia</th></tr></thead><tbody>
            @forelse($existencias as $existencia)
                <tr class="border-t border-slate-200 text-slate-800 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800/60"><td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold">{{ $existencia->producto->sku }}</td><td class="px-4 py-3 font-medium text-slate-950 dark:text-white">{{ $existencia->producto->nombre }}</td><td class="px-4 py-3">{{ $existencia->ubicacion?->nombre ?? '—' }}</td><td class="px-4 py-3">{{ $existencia->lote?->numero_lote ?? '—' }}</td><td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format((float) $existencia->cantidad, 3) }} {{ $existencia->producto->unidad_medida }}</td></tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-600 dark:text-slate-300">No hay existencias para mostrar.</td></tr>
            @endforelse
        </tbody></table></div></div>{{ $existencias->links() }}
    </section>

    <section class="space-y-4"><div><h2 class="text-lg font-bold text-slate-950 dark:text-white">Movimientos recientes</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Entradas y salidas registradas en este almacén.</p></div><div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Producto</th><th class="px-4 py-3 text-right">Cantidad</th></tr></thead><tbody>@forelse($movimientos as $movimiento)<tr class="border-t border-slate-200 text-slate-800 dark:border-slate-800 dark:text-slate-200"><td class="whitespace-nowrap px-4 py-3">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $movimiento->tipo) }}</td><td class="px-4 py-3 font-medium text-slate-950 dark:text-white">{{ $movimiento->producto?->nombre }}</td><td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format((float) $movimiento->cantidad, 3) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-600 dark:text-slate-300">No hay movimientos todavía.</td></tr>@endforelse</tbody></table></div></div></section>
</div>

<div class="space-y-7">
    @php
        $carrito = collect($lineas)->map(function ($linea) use ($productos) {
            $producto = $productos->firstWhere('id', (int) ($linea['producto_id'] ?? 0));
            return ['producto' => $producto, 'cantidad' => (float) ($linea['cantidad'] ?: 0)];
        })->filter(fn ($linea) => $linea['producto'] !== null);
        $totalCarrito = $carrito->sum(fn ($linea) => (float) $linea['producto']->precio_venta * $linea['cantidad']);
    @endphp

    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Caja · Venta nueva</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">Punto de venta</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Escanea los artículos, revisa el carrito y registra el cobro de esta caja.</p>
        </div>
        <div class="flex items-center gap-2">
        <button type="button" wire:click="$set('mostrarOperaciones', true)" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Ventas y confirmaciones <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs dark:bg-slate-800">{{ $ventas->total() }}</span></button>
        @unless (auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <a href="{{ route('establecimientos.index') }}" wire:navigate class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:self-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg> Cambiar almacén
        </a>
        @endunless
        </div>
    </header>

    @if ($ventaCompletada)
        <section role="status" class="flex flex-col justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-100 sm:flex-row sm:items-center">
            <div><p class="font-bold">Venta cobrada correctamente</p><p class="mt-1 text-sm">Folio {{ $ventaCompletada['folio'] }} · Total ${{ $ventaCompletada['total'] }} MXN · Cambio ${{ $ventaCompletada['cambio'] }} MXN</p></div>
            <button type="button" wire:click="$set('ventaCompletada', null)" class="self-start rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold hover:bg-emerald-100 dark:border-emerald-800 dark:hover:bg-emerald-900 sm:self-auto">Cerrar</button>
        </section>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">
            <p class="font-semibold">Revisa la venta antes de continuar</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 p-5 dark:border-slate-800 sm:p-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div><h2 class="font-semibold text-slate-950 dark:text-white">Lector de códigos</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">El lector está listo para recibir el siguiente artículo.</p></div>
                    @unless (auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
                    <label class="relative block w-full sm:max-w-xs">
                        <span class="sr-only">Buscar por nombre o SKU</span>
                        <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                        <input type="search" wire:model.live.debounce.300ms="busquedaProducto" placeholder="Buscar producto o SKU…" class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-400 dark:focus:border-emerald-400" />
                    </label>
                    @endunless
                </div>
                <div class="mt-5 grid gap-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-950 lg:grid-cols-[minmax(0,1fr)_minmax(240px,0.8fr)]">
                    <x-barcode-scanner model="codigoEscaneado" action="buscarCodigo" label="Escanear para agregar a la venta" />
                    @unless (auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-800 dark:text-slate-100">Si el código aún no está asociado, elige el producto<select wire:model="productoCodigoId" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"><option value="0">Selecciona un producto</option>@foreach($productos as $producto)<option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</option>@endforeach</select></label>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end"><label class="block flex-1 text-xs font-semibold text-slate-800 dark:text-slate-100">Tipo<select wire:model="tipoCodigo" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"><option value="barcode">Código de barras</option><option value="qr">Código QR</option></select></label><button type="button" wire:click="guardarCodigoYAgregar" class="rounded-lg border border-blue-300 bg-blue-50 px-3.5 py-2.5 text-sm font-semibold text-blue-900 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-100 dark:hover:bg-blue-900/60">Guardar código y agregar</button></div>
                    </div>
                    @endunless
                </div>
            </div>

            <div class="p-5 sm:p-6"><div class="flex min-h-32 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center dark:border-slate-700 dark:bg-slate-950"><span class="grid size-11 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 5v14m3-14v14m3-14v14m4-14v14m3-14v14m3-14v14"/></svg></span><p class="mt-3 font-semibold text-slate-800 dark:text-slate-100">Escanea o escribe un código para agregar un producto</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Los artículos encontrados aparecerán en la venta actual.</p></div></div>
        </article>

        <aside class="xl:sticky xl:top-40">
            <form wire:submit="registrarVenta" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div><h2 class="font-semibold text-slate-950 dark:text-white">Venta actual</h2><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $carrito->count() }} {{ $carrito->count() === 1 ? 'artículo' : 'artículos' }}</p></div>
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span>
                </div>

                <div class="max-h-[48vh] min-h-32 divide-y divide-slate-100 overflow-y-auto px-5 dark:divide-slate-800">
                    @if($carrito->isEmpty())
                        <div class="py-10 text-center"><span class="mx-auto grid size-12 place-items-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span><p class="mt-3 text-sm font-semibold text-slate-800 dark:text-slate-100">Tu carrito está vacío</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Agrega productos desde el catálogo.</p></div>
                    @else
                    @foreach($lineas as $indice => $linea)
                        @php($producto = $productos->firstWhere('id', (int) ($linea['producto_id'] ?? 0)))
                        @if($producto)
                            <div wire:key="carrito-{{ $indice }}" class="space-y-3 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $producto->nombre }}</p><p class="mt-0.5 text-xs text-slate-600 dark:text-slate-300">${{ number_format((float) $producto->precio_venta, 2) }} · {{ $producto->unidad_medida }}</p></div>
                                    <button type="button" wire:click="quitarLinea({{ $indice }})" class="rounded-md p-1 text-slate-500 hover:bg-rose-50 hover:text-rose-700 dark:text-slate-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-200" aria-label="Quitar {{ $producto->nombre }}"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg></button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Cantidad<input wire:model.live="lineas.{{ $indice }}.cantidad" type="number" min="0.001" step="0.001" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" /></label>
                                    <div class="self-end pb-2 text-right text-sm font-semibold text-slate-900 dark:text-white">${{ number_format((float) $producto->precio_venta * (float) ($linea['cantidad'] ?: 0), 2) }}</div>
                                    @if($producto->requiere_serie)
                                        <label class="col-span-2 text-xs font-medium text-slate-700 dark:text-slate-200">Unidad serializada<input wire:model="lineas.{{ $indice }}.unidad_inventario_id" type="number" min="1" placeholder="ID de unidad disponible" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" /></label>
                                    @endif
                                    <label class="col-span-2 text-xs font-medium text-slate-700 dark:text-slate-200">Lote <span class="font-normal text-slate-500 dark:text-slate-400">(opcional)</span><select wire:model="lineas.{{ $indice }}.lote_id" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="">Sin lote específico</option>@foreach($lotes->where('producto_id', $producto->id) as $lote)<option value="{{ $lote->id }}">{{ $lote->numero_lote }}@if($lote->fecha_caducidad) · caduca {{ $lote->fecha_caducidad->format('d/m/Y') }}@endif</option>@endforeach</select>@if($lotes->where('producto_id', $producto->id)->isEmpty())<span class="mt-1 block font-normal text-slate-500 dark:text-slate-400">Este producto todavía no tiene lotes registrados.</span>@endif</label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @endif
                </div>

                <div class="border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between text-sm text-slate-700 dark:text-slate-200"><span>Subtotal</span><span>${{ number_format($totalCarrito, 2) }}</span></div>
                    <div class="mt-2 flex items-center justify-between text-lg font-bold text-slate-950 dark:text-white"><span>Total</span><span>${{ number_format($totalCarrito, 2) }} <span class="text-xs font-medium">MXN</span></span></div>
                    <div class="mt-5 space-y-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Método de pago
                            <select wire:model.live="metodoPago" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="transferencia">Transferencia</option><option value="otro">Otro</option>
                            </select>
                        </label>
                        @if ($metodoPago === 'efectivo')
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Efectivo recibido
                                <input wire:model.live="importeRecibido" type="number" min="0.01" step="0.01" placeholder="0.00" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
                            </label>
                            @if (is_numeric($importeRecibido) && (float) $importeRecibido > 0)
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm dark:bg-slate-900"><span class="text-slate-600 dark:text-slate-300">Cambio</span><strong class="text-slate-950 dark:text-white">${{ number_format(max(0, (float) $importeRecibido - $totalCarrito), 2) }} MXN</strong></div>
                            @endif
                        @endif
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Referencia <span class="font-normal text-slate-500">(opcional)</span>
                            <input wire:model="referenciaPago" placeholder="Folio de autorización o transferencia" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
                        </label>
                    </div>
                    <button type="button" wire:click="cobrarVenta" wire:loading.attr="disabled" wire:target="cobrarVenta" @disabled($carrito->isEmpty()) class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-400 dark:focus-visible:ring-offset-slate-900">
                        <span wire:loading.remove wire:target="cobrarVenta">Cobrar ${{ number_format($totalCarrito, 2) }} MXN</span><span wire:loading wire:target="cobrarVenta">Procesando venta…</span>
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="registrarVenta" @disabled($carrito->isEmpty()) class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Guardar como borrador</button>
                    <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">Cobrar confirma la venta, descuenta existencias y registra el pago en una sola operación.</p>
                </div>
            </form>
        </aside>
    </section>

    @if ($mostrarOperaciones)
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" wire:keydown.escape="$set('mostrarOperaciones', false)" wire:click.self="$set('mostrarOperaciones', false)" role="dialog" aria-modal="true" aria-labelledby="operaciones-title">
    <section class="max-h-[92vh] w-full max-w-5xl space-y-4 overflow-y-auto rounded-2xl bg-slate-50 p-4 shadow-2xl dark:bg-slate-950 sm:p-6">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"><div><p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Operación</p><h2 id="operaciones-title" class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Ventas y confirmaciones</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Consulta ventas recientes y confirma borradores.</p></div><button type="button" wire:click="$set('mostrarOperaciones', false)" class="grid size-9 shrink-0 place-items-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Cerrar ventana">✕</button></div>
        <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Operación</p><h2 class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Ventas recientes</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Confirma borradores y registra los pagos de ventas confirmadas.</p></div><span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $ventas->total() }} registros</span></div>
        @forelse($ventas as $venta)
            @php($pagado = (float) $venta->pagos->where('estado', 'pagado')->sum('monto'))
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-950 dark:text-white">{{ $venta->folio }}</h3><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' => $venta->estado === 'borrador', 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' => $venta->estado === 'confirmada', 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' => ! in_array($venta->estado, ['borrador', 'confirmada'], true)])>{{ ucfirst($venta->estado) }}</span></div><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $venta->created_at->format('d/m/Y H:i') }} · {{ $venta->usuario?->name }}</p></div>
                    <div class="text-left sm:text-right"><p class="text-xl font-bold text-slate-950 dark:text-white">${{ number_format((float) $venta->total, 2) }} <span class="text-xs font-medium">MXN</span></p><p class="text-xs text-slate-600 dark:text-slate-300">Pagado ${{ number_format($pagado, 2) }}</p></div>
                </div>
                <div class="mt-4 grid gap-2 border-t border-slate-100 pt-3 dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-3">@foreach($venta->detallesVenta as $detalle)<div class="flex justify-between gap-3 text-sm"><span class="min-w-0 truncate text-slate-700 dark:text-slate-200">{{ number_format((float) $detalle->cantidad, 3) }} × {{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</span><span class="shrink-0 font-medium text-slate-900 dark:text-white">${{ number_format((float) $detalle->total, 2) }}</span></div>@endforeach</div>
                @if($venta->estado === 'borrador')
                    <div class="mt-4 flex justify-end"><button type="button" wire:click="confirmar({{ $venta->id }})" wire:confirm="¿Confirmas la venta y el descuento de las existencias?" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900">Confirmar y descontar stock</button></div>
                @elseif($venta->estado === 'confirmada')
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Registrar pago</p>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Monto<input wire:model="montoPago" type="number" min="0.01" step="0.01" placeholder="0.00" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white" /></label>
                            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Método<select wire:model="metodoPago" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="transferencia">Transferencia</option><option value="otro">Otro</option></select></label>
                            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Referencia<input wire:model="referenciaPago" placeholder="Opcional" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white" /></label>
                            <button type="button" wire:click="$set('ventaPagoId', {{ $venta->id }})" class="self-end rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ $ventaPagoId === $venta->id ? 'Seleccionada' : 'Elegir venta' }}</button>
                        </div>
                        @if($ventaPagoId === $venta->id)<div class="mt-3 flex justify-end"><button type="button" wire:click="registrarPago" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">Guardar pago</button></div>@endif
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center dark:border-slate-700 dark:bg-slate-900"><p class="font-semibold text-slate-800 dark:text-slate-100">Todavía no hay ventas</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Las ventas que registres aparecerán aquí.</p></div>
        @endforelse
        <div>{{ $ventas->links() }}</div>
    </section>
    </div>
    @endif
</div>

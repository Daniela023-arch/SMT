<div class="space-y-7">
    @php
        $carrito = collect($lineas)->map(function ($linea, $indice) use ($productos) {
            $producto = $productos->firstWhere('id', (int) ($linea['producto_id'] ?? 0));
            return ['indice' => $indice, 'producto' => $producto, 'cantidad' => (float) ($linea['cantidad'] ?: 0)];
        })->filter(fn ($linea) => $linea['producto'] !== null);
        $totalCarrito = $carrito->sum(fn ($linea) => (float) $linea['producto']->precio_venta * $linea['cantidad']);
        $esPersonalVentas = auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
        $puedeGestionarVentas = auth()->user()->roles()->whereIn('roles.slug', ['admin', 'gerente'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
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
                </div>
                <div class="mt-5 grid gap-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-950 {{ $esPersonalVentas ? 'lg:grid-cols-1' : 'lg:grid-cols-[minmax(0,1fr)_minmax(240px,0.8fr)]' }}">
                    <div>
                        <x-barcode-scanner model="codigoEscaneado" action="buscarCodigo" label="Escanear" placeholder="Escanea el código de barras o QR" :camera="false" :submit="false" status="Conecta el lector USB/Bluetooth y escanea el producto; se agregará automáticamente." />
                        <div class="relative mt-4">
                            <label for="buscar-producto-pos" class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-200">Buscar producto por nombre o SKU</label>
                            <div class="relative">
                                <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                                <input id="buscar-producto-pos" type="search" wire:model.live.debounce.250ms="busquedaProducto" autocomplete="off" placeholder="Escribe el nombre o SKU…" class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-400" />
                            </div>
                            @if (filled($busquedaProducto))
                                <div class="mt-2 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" role="listbox" aria-label="Resultados de productos">
                                    @forelse ($productos as $resultado)
                                        <button type="button" wire:key="buscar-pos-{{ $resultado->id }}" wire:click="agregarProductoDesdeBusqueda({{ $resultado->id }})" class="flex w-full items-center justify-between gap-3 border-b border-slate-100 px-3 py-2.5 text-left last:border-b-0 hover:bg-blue-50 focus-visible:bg-blue-50 focus-visible:outline-none dark:border-slate-800 dark:hover:bg-slate-800 dark:focus-visible:bg-slate-800" role="option">
                                            <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $resultado->nombre }}</span><span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $resultado->sku }}</span></span>
                                            <span class="shrink-0 text-right"><span class="block text-sm font-bold text-slate-900 dark:text-white">${{ number_format((float) $resultado->precio_venta, 2) }}</span><span class="text-[11px] font-semibold text-blue-700 dark:text-blue-300">Agregar +</span></span>
                                        </button>
                                    @empty
                                        <p class="px-3 py-4 text-sm text-slate-500 dark:text-slate-400">No hay productos con ese nombre o SKU.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                    @unless (auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-800 dark:text-slate-100">Si el código aún no está asociado, elige el producto<select wire:model="productoCodigoId" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"><option value="0">Selecciona un producto</option>@foreach($productos as $producto)<option value="{{ $producto->id }}">{{ $producto->sku }} · {{ $producto->nombre }}</option>@endforeach</select></label>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end"><label class="block flex-1 text-xs font-semibold text-slate-800 dark:text-slate-100">Tipo<select wire:model="tipoCodigo" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"><option value="barcode">Código de barras</option><option value="qr">Código QR</option></select></label><button type="button" wire:click="guardarCodigoYAgregar" class="rounded-lg border border-blue-300 bg-blue-50 px-3.5 py-2.5 text-sm font-semibold text-blue-900 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-100 dark:hover:bg-blue-900/60">Guardar código y agregar</button></div>
                    </div>
                    @endunless
                </div>
            </div>

            <div class="border-t border-slate-200 p-5 dark:border-slate-800 sm:p-6">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div><h2 class="font-semibold text-slate-950 dark:text-white">Productos agregados</h2><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Los artículos escaneados aparecerán aquí.</p></div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $carrito->count() }} {{ $carrito->count() === 1 ? 'producto' : 'productos' }}</span>
                </div>
                @if ($carrito->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3" aria-live="polite">
                        @foreach ($carrito as $indiceArticulo => $articulo)
                            @php($productoAgregado = $articulo['producto'])
                            <article wire:key="producto-agregado-{{ $productoAgregado->id }}-{{ $indiceArticulo }}" class="flex min-h-28 items-center gap-3 rounded-xl border border-emerald-200 bg-white p-4 shadow-sm transition dark:border-emerald-900/70 dark:bg-slate-900">
                                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z"/><path d="m4 7 8 4 8-4M12 11v10"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $productoAgregado->nombre }}</span>
                                    <span class="mt-1 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $productoAgregado->sku }}</span>
                                    <span class="mt-2 flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Cantidad: {{ number_format($articulo['cantidad'], 3) }} {{ $productoAgregado->unidad_medida }}</span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">${{ number_format((float) $productoAgregado->precio_venta * $articulo['cantidad'], 2) }}</span>
                                    </span>
                                    @if ($productoAgregado->requiere_serie)
                                        <label class="mt-3 block text-xs font-semibold text-slate-700 dark:text-slate-200">Unidad serializada
                                            <select wire:model="lineas.{{ $articulo['indice'] }}.unidad_inventario_id" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                                <option value="">Selecciona número de serie</option>
                                                @foreach ($unidadesPorProducto->get($productoAgregado->id, collect()) as $unidad)
                                                    <option value="{{ $unidad->id }}">{{ $unidad->numero_serie ?: $unidad->codigo_activo ?: 'Unidad '.$unidad->id }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                </div>
                                <button type="button" wire:click="prepararQuitarLinea({{ $articulo['indice'] }})" class="grid size-9 shrink-0 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-rose-900 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" aria-label="Quitar {{ $productoAgregado->nombre }} del carrito">
                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
                                </button>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="flex min-h-40 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center dark:border-slate-700 dark:bg-slate-950">
                        <span class="grid size-11 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 5v14m3-14v14m3-14v14m4-14v14m3-14v14m3-14v14"/></svg></span>
                        <p class="mt-3 font-semibold text-slate-800 dark:text-slate-100">Aún no agregas productos</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Escanea un código para que el producto aparezca aquí.</p>
                    </div>
                @endif
            </div>
        </article>

        <aside class="xl:sticky xl:top-40">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div><h2 class="font-semibold text-slate-950 dark:text-white">Venta actual</h2><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $carrito->count() }} {{ $carrito->count() === 1 ? 'artículo' : 'artículos' }}</p></div>
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span>
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
                    <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">Cobrar confirma la venta, descuenta existencias y registra el pago en una sola operación.</p>
                </div>
            </div>
        </aside>
    </section>

    @if ($mostrarAutorizacionRetiro)
        @php($productoPendienteRetiro = $productos->firstWhere('id', $productoRetiroId))
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" wire:keydown.escape="cancelarAutorizacionRetiro" wire:click.self="cancelarAutorizacionRetiro" role="dialog" aria-modal="true" aria-labelledby="autorizacion-retiro-title">
            <form wire:submit="autorizarYQuitarLinea" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900 sm:p-6">
                <div class="flex items-start gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3"/></svg></span><div><h2 id="autorizacion-retiro-title" class="font-bold text-slate-950 dark:text-white">Autorización de gerencia</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">El gerente o administrador debe estar presente e ingresar sus credenciales para quitar el producto del carrito.</p></div></div>
                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-950"><p class="font-semibold text-slate-900 dark:text-white">{{ $productoPendienteRetiro?->nombre ?? 'Producto no disponible' }}</p><p class="mt-0.5 text-slate-600 dark:text-slate-300">{{ $productoPendienteRetiro?->sku }} · Cantidad {{ number_format($cantidadRetiro, 3) }} · ${{ number_format((float) ($productoPendienteRetiro?->precio_venta ?? 0) * $cantidadRetiro, 2) }}</p></div>
                <label class="mt-5 block text-sm font-medium text-slate-800 dark:text-slate-100">Correo del gerente o administrador<input wire:model="correoGerente" type="email" autocomplete="username" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" /></label>
                <label class="mt-3 block text-sm font-medium text-slate-800 dark:text-slate-100">Contraseña<input wire:model="contrasenaGerente" type="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" /></label>
                @error('contrasenaGerente')<p class="mt-1 text-xs font-medium text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
                @error('lineaRetiroIndice')<p class="mt-1 text-xs font-medium text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
                <label class="mt-3 block text-sm font-medium text-slate-800 dark:text-slate-100">Motivo<textarea wire:model="motivoRetiroLinea" rows="2" required minlength="5" maxlength="500" placeholder="Ej. Se escaneó por error" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea></label>
                @error('motivoRetiroLinea')<p class="mt-1 text-xs font-medium text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
                <div class="mt-5 flex justify-end gap-2"><button type="button" wire:click="cancelarAutorizacionRetiro" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</button><button type="submit" wire:loading.attr="disabled" wire:target="autorizarYQuitarLinea" class="rounded-lg bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-800 disabled:opacity-60">Autorizar y quitar</button></div>
            </form>
        </div>
    @endif

    @if ($mostrarOperaciones)
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" wire:keydown.escape="$set('mostrarOperaciones', false)" wire:click.self="$set('mostrarOperaciones', false)" role="dialog" aria-modal="true" aria-labelledby="operaciones-title">
    <section class="max-h-[92vh] w-full max-w-5xl space-y-4 overflow-y-auto rounded-2xl bg-slate-50 p-4 shadow-2xl dark:bg-slate-950 sm:p-6">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"><div><p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Operación</p><h2 id="operaciones-title" class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Ventas y confirmaciones</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $puedeGestionarVentas ? 'Consulta ventas, confirma borradores y registra pagos.' : 'Consulta la información de ventas de este punto de venta.' }}</p></div><button type="button" wire:click="$set('mostrarOperaciones', false)" class="grid size-9 shrink-0 place-items-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Cerrar ventana">✕</button></div>

        <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Operación</p><h2 class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Ventas recientes</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $puedeGestionarVentas ? 'Confirma borradores y registra los pagos de ventas confirmadas.' : 'Consulta folios, productos, importes y estado de las ventas.' }}</p></div><span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $ventas->total() }} registros</span></div>
        @forelse($ventas as $venta)
            @php($pagado = (float) $venta->pagos->where('estado', 'pagado')->sum('monto'))
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-950 dark:text-white">{{ $venta->folio }}</h3><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' => $venta->estado === 'borrador', 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' => $venta->estado === 'confirmada', 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' => ! in_array($venta->estado, ['borrador', 'confirmada'], true)])>{{ ucfirst($venta->estado) }}</span></div><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $venta->created_at->format('d/m/Y H:i') }} · {{ $venta->usuario?->name }}</p></div>
                    <div class="text-left sm:text-right"><p class="text-xl font-bold text-slate-950 dark:text-white">${{ number_format((float) $venta->total, 2) }} <span class="text-xs font-medium">MXN</span></p><p class="text-xs text-slate-600 dark:text-slate-300">Pagado ${{ number_format($pagado, 2) }}</p></div>
                </div>
                <div class="mt-4 grid gap-2 border-t border-slate-100 pt-3 dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-3">@foreach($venta->detallesVenta as $detalle)<div class="flex justify-between gap-3 text-sm"><span class="min-w-0 truncate text-slate-700 dark:text-slate-200">{{ number_format((float) $detalle->cantidad, 3) }} × {{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</span><span class="shrink-0 font-medium text-slate-900 dark:text-white">${{ number_format((float) $detalle->total, 2) }}</span></div>@endforeach</div>
                @if($venta->estado === 'borrador' && $puedeGestionarVentas)
                    <div class="mt-4 flex justify-end"><button type="button" wire:click="confirmar({{ $venta->id }})" wire:confirm="¿Confirmas la venta y el descuento de las existencias?" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900">Confirmar y descontar stock</button></div>
                @elseif($venta->estado === 'confirmada' && $puedeGestionarVentas)
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

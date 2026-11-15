<x-layouts::app :title="'Inicio | '.$tenant->nombre">
    @php
        $brand = $tenant->configuracionTenant?->color_primario ?: '#173d83';
    @endphp

    <div class="space-y-7" style="--tenant-primary: {{ $brand }}">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold" style="color: var(--tenant-primary)">
                    @if ($esGerente) Panel de gerente · {{ $sucursalesEnAlcance }} sucursal(es) asignada(s)
                    @elseif ($esAdmin) Administración del tenant
                    @elseif ($esAlmacen) Operación de almacén · {{ $almacenesOperativos->count() }} almacén(es) asignado(s)
                    @else Ferretería multisucursal @endif
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $esGerente ? 'Panel del gerente' : ($esAlmacen ? 'Panel de almacén' : 'Panel de '.$tenant->nombre) }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    @if ($esGerente) Supervisa personal, ventas e inventario únicamente en las sucursales que tienes asignadas.
                    @elseif ($esAdmin && $personalActivo === 0) No tienes personal operativo activo. Puedes encargarte directamente de ventas e inventario desde cada almacén.
                    @elseif ($esAdmin) Supervisa el tenant y entra a ventas o inventario de cualquiera de tus almacenes.
                    @elseif ($esAlmacen) Consulta y opera solo los almacenes que te asignaron.
                    @else Resumen de operación, inventario y actividad reciente. @endif
                </p>
            </div>
            @if ($esAdmin || $esGerente)
                <a href="{{ route('tenant.usuarios') }}" wire:navigate class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ $personalActivo > 0 ? 'Administrar personal' : 'Agregar personal' }}</a>
            @endif
        </header>

        @if ($esAdmin && $personalActivo === 0)
            <section class="flex flex-col justify-between gap-4 rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/40 sm:flex-row sm:items-center">
                <div><h2 class="font-bold text-blue-950 dark:text-blue-100">El administrador está a cargo de la operación</h2><p class="mt-1 text-sm text-blue-900 dark:text-blue-200">Abre el inventario para registrar existencias o entra al punto de venta para atender una compra. Puedes agregar gerente o encargados cuando lo necesites.</p></div>
                <a href="{{ route('establecimientos.index') }}" wire:navigate class="shrink-0 rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-800">Ver almacenes</a>
            </section>
        @endif

        @if ($esGerente)
            <section class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-900 dark:bg-indigo-950/40">
                <h2 class="font-bold text-indigo-950 dark:text-indigo-100">Tu espacio de gerencia</h2>
                <p class="mt-1 text-sm text-indigo-900 dark:text-indigo-200">Puedes organizar al personal y operar las sucursales asignadas. Los planes, cobros y configuración general siguen bajo control del administrador del tenant.</p>
            </section>
        @endif

        <section aria-label="Indicadores principales" class="grid gap-4 md:grid-cols-2">
            @foreach ([
                ['Registros en catálogo', number_format($totalProductos), 'Productos activos', '▦'],
                ['Movimientos registrados', number_format($movimientos->count()), 'Actividad reciente de inventario', '↗'],
            ] as [$label, $value, $caption, $icon])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
                            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $caption }}</p>
                        </div>
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl text-xl" style="background: color-mix(in srgb, var(--tenant-primary) 10%, white); color: var(--tenant-primary)" aria-hidden="true">{{ $icon }}</span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-slate-900 dark:text-white">Artículos que requieren atención</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Productos en el mínimo o por debajo de su existencia mínima.</p>
                    </div>
                    <a href="{{ route('productos.index') }}" wire:navigate class="shrink-0 text-sm font-semibold hover:underline" style="color: var(--tenant-primary)">Catálogo</a>
                </div>
                @forelse ($productosBajoStock as $producto)
                    <div class="flex items-center justify-between gap-4 border-t border-slate-100 py-3 dark:border-slate-800">
                        <div class="min-w-0"><p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $producto->nombre }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $producto->sku }}</p></div>
                        <div class="shrink-0 text-right"><p class="text-sm font-semibold text-amber-700 dark:text-amber-300">{{ number_format((float) $producto->existencia_actual, 0) }} disponibles</p><p class="text-xs text-slate-500 dark:text-slate-400">Mínimo {{ number_format((float) $producto->stock_minimo, 0) }}</p></div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-950"><p class="text-sm font-medium text-slate-700 dark:text-slate-200">Sin alertas de stock.</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No hay productos activos debajo de su mínimo.</p></div>
                @endforelse
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-4"><h2 class="font-semibold text-slate-900 dark:text-white">Movimientos recientes</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Entradas, salidas y otros movimientos del negocio.</p></div>
                @forelse ($movimientos as $movimiento)
                    <div class="flex items-center gap-3 border-t border-slate-100 py-3 dark:border-slate-800">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full text-sm font-bold" style="background: color-mix(in srgb, var(--tenant-primary) 10%, white); color: var(--tenant-primary)" aria-hidden="true">{{ strtoupper(substr($movimiento->tipo, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', ucfirst($movimiento->tipo)) }} · {{ $movimiento->created_at?->diffForHumans() }}</p></div>
                        <span class="shrink-0 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ number_format((float) $movimiento->cantidad, 0) }}</span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-950"><p class="text-sm font-medium text-slate-700 dark:text-slate-200">Aún no hay operaciones.</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Los movimientos aparecerán aquí cuando se registren.</p></div>
                @endforelse
            </article>
        </section>

        @if ($esAdmin || $esGerente || $esAlmacen)
            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div><h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ $esGerente ? 'Operación de tus sucursales' : ($esAlmacen ? 'Mis almacenes asignados' : 'Ventas e inventario por almacén') }}</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $esAlmacen ? 'Elige el almacén en el que vas a trabajar.' : 'Entra directamente a la caja o al inventario de una ubicación.' }}</p></div>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $almacenesOperativos->count() }} almacén(es)</span>
                </div>
                @forelse ($almacenesOperativos as $almacen)
                    <article class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center">
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $almacen->establecimiento?->nombre }}</p><h3 class="mt-1 font-bold text-slate-950 dark:text-white">{{ $almacen->nombre }}</h3><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Código {{ $almacen->codigo }}</p></div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('inventario.show', [$almacen->establecimiento_id, $almacen->id]) }}" wire:navigate class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Abrir inventario</a>
                            @if ($esAdmin || $esGerente)
                                <a href="{{ route('ventas.index', [$almacen->establecimiento_id, $almacen->id]) }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">Abrir punto de venta</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-9 text-center dark:border-slate-700 dark:bg-slate-900"><p class="font-semibold text-slate-800 dark:text-slate-100">Aún no hay almacenes disponibles</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $esGerente ? 'Puedes agregar un almacén a tus sucursales asignadas. Si no aparece la sucursal, pide al administrador que te la asigne.' : 'Crea una sucursal y su almacén desde Sucursales para empezar a operar.' }}</p>@if ($esAdmin || $esGerente)<a href="{{ route('establecimientos.index') }}" wire:navigate class="mt-4 inline-flex rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white">{{ $esGerente ? 'Administrar mis sucursales' : 'Configurar sucursales' }}</a>@endif</div>
                @endforelse
            </section>
        @endif

        @unless ($esAlmacen)
            <section aria-label="Resumen de ventas">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Ventas confirmadas hoy</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($ventasHoyCantidad) }} <span class="text-base font-medium">· ${{ number_format($ventasHoyTotal, 2) }} MXN</span></p>
                </article>
            </section>
        @endunless
    </div>
</x-layouts::app>

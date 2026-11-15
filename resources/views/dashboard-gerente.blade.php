<x-layouts::app :title="'Panel del gerente | '.$tenant->nombre">
    @php($brand = $tenant->configuracionTenant?->color_primario ?: '#173d83')

    <div class="space-y-7" style="--tenant-primary: {{ $brand }}">
        <header class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:p-7">
            <div>
                <p class="text-sm font-semibold" style="color: var(--tenant-primary)">Gerencia · {{ $tenant->nombre }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">Panel del gerente</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Supervisa las sucursales asignadas, coordina al personal y da seguimiento a la operación diaria.</p>
            </div>
            <a href="{{ route('tenant.usuarios') }}" wire:navigate class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-800">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1m3-11v6m-3-3h6"/></svg>
                Gestionar personal
            </a>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen de sucursales asignadas">
            @foreach ([
                ['Sucursales a cargo', number_format($sucursalesEnAlcance), 'Asignadas por el administrador', '⌂'],
                ['Personal activo', number_format($personalGerenteTotal), 'Ventas y almacén en tu alcance', '♙'],
                ['Ventas de hoy', number_format($ventasHoyCantidad), 'En tus sucursales', '$'],
                ['Movimientos recientes', number_format($movimientos->count()), 'Actividad de inventario', '↗'],
            ] as [$label, $value, $caption, $icon])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p><p class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $value }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $caption }}</p></div><span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-lg font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-200" aria-hidden="true">{{ $icon }}</span></div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.65fr)]">
            <article class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div><h2 class="text-lg font-bold text-slate-950 dark:text-white">Sucursales y almacenes asignados</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Accede al inventario o abre el punto de venta de cada ubicación.</p></div>
                    <a href="{{ route('establecimientos.index') }}" wire:navigate class="text-sm font-semibold text-blue-700 hover:underline dark:text-blue-300">Ver sucursales</a>
                </div>
                @forelse ($almacenesOperativos as $almacen)
                    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 items-start gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m3 10 9-7 9 7v10H3V10Z"/><path d="M9 20v-7h6v7"/></svg></span><div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $almacen->establecimiento?->nombre }}</p><h3 class="mt-1 truncate font-bold text-slate-950 dark:text-white">{{ $almacen->nombre }}</h3><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Código {{ $almacen->codigo }}</p></div></div>
                        <div class="flex shrink-0 flex-wrap gap-2 pl-[52px] sm:pl-0">
                            <a href="{{ route('inventario.show', [$almacen->establecimiento_id, $almacen->id]) }}" wire:navigate class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Inventario</a>
                            <a href="{{ route('ventas.index', [$almacen->establecimiento_id, $almacen->id]) }}" wire:navigate class="rounded-lg bg-emerald-700 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">Abrir punto de venta</a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-9 text-center dark:border-slate-700 dark:bg-slate-900"><p class="font-semibold text-slate-800 dark:text-slate-100">No hay almacenes activos en tus sucursales</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Puedes crear un almacén desde Sucursales. Si la sucursal no aparece, solicita al administrador que te la asigne.</p><a href="{{ route('establecimientos.index') }}" wire:navigate class="mt-4 inline-flex rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white">Administrar sucursales</a></div>
                @endforelse
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-950 dark:text-white">Personal a tu cargo</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Solo personas asignadas a tus sucursales.</p></div><a href="{{ route('tenant.usuarios') }}" wire:navigate class="shrink-0 text-sm font-semibold text-blue-700 hover:underline dark:text-blue-300">Administrar</a></div>
                @forelse ($equipoGerente as $persona)
                    @php($rolPersona = $persona->roles->first()?->slug)
                    <div class="flex items-center gap-3 border-t border-slate-100 py-3 first:mt-4 dark:border-slate-800">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-50 text-xs font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ $persona->initials() }}</span>
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $persona->name }}</p><p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $persona->establecimientos->pluck('nombre')->join(', ') }}</p></div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $rolPersona === 'ventas' ? 'Ventas' : 'Almacén' }}</span>
                    </div>
                @empty
                    <div class="mt-5 rounded-xl bg-slate-50 px-4 py-6 text-center dark:bg-slate-950"><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Aún no tienes personal asignado</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Agrega encargados de ventas o almacén para tus sucursales.</p><a href="{{ route('tenant.usuarios') }}" wire:navigate class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:underline dark:text-blue-300">Agregar personal</a></div>
                @endforelse
                @if ($personalGerenteTotal > $equipoGerente->count())
                    <a href="{{ route('tenant.usuarios') }}" wire:navigate class="mt-2 block border-t border-slate-100 pt-3 text-center text-sm font-semibold text-blue-700 hover:underline dark:border-slate-800 dark:text-blue-300">Ver los {{ $personalGerenteTotal }} usuarios asignados</a>
                @endif
            </article>
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-3"><h2 class="font-bold text-slate-950 dark:text-white">Productos que requieren atención</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Stock bajo en tus sucursales asignadas.</p></div>
                @forelse ($productosBajoStock as $producto)
                    <div class="flex items-center justify-between gap-4 border-t border-slate-100 py-3 dark:border-slate-800"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $producto->nombre }}</p><p class="text-xs text-slate-500 dark:text-slate-400">{{ $producto->sku }}</p></div><p class="shrink-0 text-right text-sm font-semibold text-amber-700 dark:text-amber-300">{{ number_format((float) $producto->existencia_actual, 0) }} disponibles</p></div>
                @empty
                    <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-300">No hay alertas de stock en este momento.</p>
                @endforelse
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-3"><h2 class="font-bold text-slate-950 dark:text-white">Actividad reciente</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Movimientos de los almacenes dentro de tu alcance.</p></div>
                @forelse ($movimientos as $movimiento)
                    <div class="flex items-center gap-3 border-t border-slate-100 py-3 dark:border-slate-800"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-blue-50 text-xs font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ strtoupper(substr($movimiento->tipo, 0, 1)) }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</p><p class="text-xs text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', ucfirst($movimiento->tipo)) }} · {{ $movimiento->created_at?->diffForHumans() }}</p></div><span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ number_format((float) $movimiento->cantidad, 0) }}</span></div>
                @empty
                    <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-300">Todavía no hay movimientos registrados.</p>
                @endforelse
            </article>
        </section>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400">Los cambios de plan, pagos y configuración general son exclusivos del administrador del tenant.</p>
    </div>
</x-layouts::app>

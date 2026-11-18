<div class="space-y-8">
    @php
        $esAdmin = auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
        $esGerente = auth()->user()->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
        $puedeVerAlmacen = $esAdmin || ! $esGerente || auth()->user()->puede_ver_almacen;
        $puedeVerPuntoVenta = $esAdmin || ! $esGerente || auth()->user()->puede_ver_punto_venta;
        $totalAlmacenes = $establecimientos->sum(fn ($establecimiento) => $establecimiento->almacenes->count());
    @endphp

    <header class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Organización</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">Sucursales y almacenes</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Elige una ubicación para {{ $puedeVerAlmacen && $puedeVerPuntoVenta ? 'consultar existencias o abrir la caja de ventas' : ($puedeVerAlmacen ? 'consultar existencias' : 'abrir la caja de ventas') }}.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:min-w-64">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs font-medium text-slate-600 dark:text-slate-300">Establecimientos</p><p class="mt-1 text-xl font-bold text-slate-950 dark:text-white">{{ $establecimientos->count() }}</p></div>
            @if($puedeVerAlmacen)<div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs font-medium text-slate-600 dark:text-slate-300">Almacenes</p><p class="mt-1 text-xl font-bold text-slate-950 dark:text-white">{{ $totalAlmacenes }}</p></div>@endif
        </div>
    </header>

    @if($esAdmin || ($esGerente && $puedeVerAlmacen))
        <section class="grid gap-4 xl:grid-cols-2">
            @if($esAdmin)
            <form wire:submit="crear" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div><h2 class="font-semibold text-slate-950 dark:text-white">Nuevo establecimiento</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra una sucursal o centro de distribución.</p></div>
                <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="nombre" label="Nombre" placeholder="Ej. Sucursal Centro" required /><flux:input wire:model="codigo" label="Código" placeholder="Ej. CENTRO" required /></div>
                <flux:select wire:model="tipo" label="Tipo"><flux:select.option value="sucursal">Sucursal</flux:select.option><flux:select.option value="centro_distribucion">Centro de distribución</flux:select.option></flux:select>
                <flux:textarea wire:model="direccion" label="Dirección" placeholder="Calle, número, colonia y ciudad" />
                <flux:button type="submit" variant="primary">Crear establecimiento</flux:button>
            </form>
            @endif

            <form wire:submit="crearAlmacen" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div><h2 class="font-semibold text-slate-950 dark:text-white">Agregar almacén</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $esAdmin ? 'Cada almacén contará con una ubicación principal.' : 'Solo puedes agregar almacenes dentro de las sucursales que tienes asignadas.' }}</p></div>
                <flux:select wire:model="establecimientoSeleccionado" label="Establecimiento" required><flux:select.option value="0">Selecciona una sucursal o CEDIS</flux:select.option>@foreach($establecimientos as $establecimiento)<flux:select.option value="{{ $establecimiento->id }}">{{ $establecimiento->nombre }}</flux:select.option>@endforeach</flux:select>
                <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="nombreAlmacen" label="Nombre del almacén" placeholder="Ej. Almacén principal" required /><flux:input wire:model="codigoAlmacen" label="Código" placeholder="Ej. CENTRAL" required /></div>
                <div class="pt-1"><flux:button type="submit" variant="primary">Crear almacén</flux:button></div>
            </form>

            <form wire:submit="crearUbicacion" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div><h2 class="font-semibold text-slate-950 dark:text-white">Agregar ubicación interna</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Organiza el almacén por pasillos, racks o estantes.</p></div>
                <flux:select wire:model="almacenSeleccionado" label="Almacén" required><flux:select.option value="0">Selecciona un almacén</flux:select.option>@foreach($establecimientos as $establecimiento)@foreach($establecimiento->almacenes as $almacen)<flux:select.option value="{{ $almacen->id }}">{{ $establecimiento->nombre }} · {{ $almacen->nombre }}</flux:select.option>@endforeach @endforeach</flux:select>
                <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="nombreUbicacion" label="Nombre" placeholder="Ej. Pasillo 2, Rack A" required /><flux:input wire:model="codigoUbicacion" label="Código (opcional)" /></div>
                <flux:button type="submit" variant="primary">Guardar ubicación</flux:button>
            </form>

        </section>
    @endif

    @if(auth()->user()->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <form wire:submit="crearUbicacion" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div><h2 class="font-semibold text-slate-950 dark:text-white">Administrar ubicaciones</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Agrega pasillos, racks o estantes a tus almacenes asignados.</p></div>
            <div class="grid gap-4 md:grid-cols-3"><flux:select wire:model="almacenSeleccionado" label="Almacén asignado" required><flux:select.option value="0">Selecciona un almacén</flux:select.option>@foreach($establecimientos as $establecimiento)@foreach($establecimiento->almacenes as $almacen)<flux:select.option value="{{ $almacen->id }}">{{ $establecimiento->nombre }} · {{ $almacen->nombre }}</flux:select.option>@endforeach @endforeach</flux:select><flux:input wire:model="nombreUbicacion" label="Nombre de la ubicación" required /><flux:input wire:model="codigoUbicacion" label="Código (opcional)" /></div>
            <flux:button type="submit" variant="primary">Crear ubicación</flux:button>
        </form>
    @endif

    <section class="space-y-4">
        <div><h2 class="text-lg font-bold text-slate-950 dark:text-white">Estructura de la empresa</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Accesos rápidos a {{ $puedeVerAlmacen && $puedeVerPuntoVenta ? 'inventario y punto de venta' : ($puedeVerAlmacen ? 'inventario' : 'punto de venta') }}.</p></div>
        @forelse($establecimientos as $establecimiento)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col justify-between gap-4 border-b border-slate-100 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-950/60 sm:flex-row sm:items-center sm:px-6">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 10h18l-2-6H5l-2 6Zm2 0v10h14V10m-9 10v-6h4v6"/><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg></span>
                        <div><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-950 dark:text-white">{{ $establecimiento->nombre }}</h3><span @class(['rounded-full px-2.5 py-1 text-[11px] font-semibold', 'bg-emerald-100 text-blue-900 dark:bg-emerald-950 dark:text-emerald-200' => $establecimiento->tipo === 'sucursal', 'bg-violet-100 text-violet-900 dark:bg-violet-950 dark:text-violet-200' => $establecimiento->tipo === 'centro_distribucion'])>{{ $establecimiento->tipo === 'centro_distribucion' ? 'Centro de distribución' : 'Sucursal' }}</span></div><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Código {{ $establecimiento->codigo }}{{ $establecimiento->direccion ? ' · '.$establecimiento->direccion : '' }}</p></div>
                    </div>
                    <span @class(['inline-flex items-center gap-2 self-start rounded-full px-3 py-1.5 text-xs font-semibold sm:self-auto', 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' => $establecimiento->activo, 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' => ! $establecimiento->activo])><span @class(['size-2 rounded-full', 'bg-emerald-600 dark:bg-emerald-400' => $establecimiento->activo, 'bg-slate-500' => ! $establecimiento->activo])></span>{{ $establecimiento->activo ? 'Activo' : 'Inactivo' }}</span>
                </div>

                <div class="space-y-3 p-4 sm:p-5">
                    @forelse($establecimiento->almacenes as $almacen)
                        <div class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-7h6v7m-8-9h.01M12 12h.01m4-.01h.01"/></svg></span>
                                <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h4 class="font-semibold text-slate-900 dark:text-white">{{ $almacen->nombre }}</h4><span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $almacen->codigo }}</span></div><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $almacen->ubicaciones->pluck('nombre')->join(' · ') ?: 'Ubicación principal' }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $almacen->ubicaciones->count() }} {{ $almacen->ubicaciones->count() === 1 ? 'ubicación' : 'ubicaciones' }}</p></div>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2 pl-[52px] sm:pl-0">
                                @if($puedeVerAlmacen && ($esAdmin || $esGerente || auth()->user()->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists()))<a wire:navigate href="{{ route('inventario.show', [$establecimiento->id, $almacen->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-emerald-50 hover:text-blue-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-700 dark:hover:bg-emerald-950/50 dark:hover:text-blue-100">
                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V5m0 14h16M8 15v-4h3v4m2 0V7h3v8m2 0v-2h3v2"/></svg> Inventario
                                </a>@endif
                                @if($puedeVerPuntoVenta && auth()->user()->roles()->whereIn('roles.slug', ['admin', 'gerente', 'ventas'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
                                    <a wire:navigate href="{{ route('ventas.index', [$establecimiento->id, $almacen->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900">
                                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg> Abrir punto de venta
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center dark:border-slate-700"><p class="text-sm font-medium text-slate-800 dark:text-slate-100">Aún no hay almacenes</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Cuando se agregue uno, aparecerá aquí.</p></div>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center dark:border-slate-700 dark:bg-slate-900"><p class="font-semibold text-slate-800 dark:text-slate-100">Aún no hay establecimientos registrados</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Solicita al administrador que configure la primera sucursal.</p></div>
        @endforelse
    </section>
</div>

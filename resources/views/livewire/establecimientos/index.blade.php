<div class="mx-auto max-w-7xl space-y-8 p-6">
    <div>
        <flux:heading size="xl">Establecimientos</flux:heading>
        <flux:subheading>Administra sucursales, centros de distribución, almacenes y asignaciones de acceso.</flux:subheading>
    </div>

    @if(auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <section class="grid gap-6 lg:grid-cols-2">
            <form wire:submit="crear" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Nuevo establecimiento</flux:heading>
                <flux:input wire:model="codigo" label="Código" required />
                <flux:input wire:model="nombre" label="Nombre" required />
                <flux:select wire:model="tipo" label="Tipo">
                    <flux:select.option value="sucursal">Sucursal</flux:select.option>
                    <flux:select.option value="centro_distribucion">Centro de distribución</flux:select.option>
                </flux:select>
                <flux:textarea wire:model="direccion" label="Dirección" />
                <flux:button type="submit" variant="primary">Crear establecimiento</flux:button>
            </form>

            <form wire:submit="crearAlmacen" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Agregar almacén</flux:heading>
                <flux:select wire:model="establecimientoSeleccionado" label="Establecimiento" required>
                    <flux:select.option value="0">Selecciona una sucursal o CEDIS</flux:select.option>
                    @foreach($establecimientos as $establecimiento)
                        <flux:select.option value="{{ $establecimiento->id }}">{{ $establecimiento->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="codigoAlmacen" label="Código del almacén" required />
                <flux:input wire:model="nombreAlmacen" label="Nombre del almacén" required />
                <flux:button type="submit" variant="primary">Crear almacén</flux:button>
            </form>

            <form wire:submit="crearUbicacion" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Agregar ubicación</flux:heading>
                <flux:select wire:model="almacenSeleccionado" label="Almacén" required>
                    <flux:select.option value="0">Selecciona un almacén</flux:select.option>
                    @foreach($establecimientos as $establecimiento)
                        @foreach($establecimiento->almacenes as $almacen)
                            <flux:select.option value="{{ $almacen->id }}">{{ $establecimiento->nombre }} · {{ $almacen->nombre }}</flux:select.option>
                        @endforeach
                    @endforeach
                </flux:select>
                <flux:input wire:model="nombreUbicacion" label="Nombre (pasillo, rack, estante)" required />
                <flux:input wire:model="codigoUbicacion" label="Código (opcional)" />
                <flux:button type="submit" variant="primary">Crear ubicación</flux:button>
            </form>

            <form wire:submit="asignarUsuario" class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg">Asignar usuario</flux:heading>
                <flux:select wire:model="establecimientoSeleccionado" label="Establecimiento" required>
                    <flux:select.option value="0">Selecciona un establecimiento</flux:select.option>
                    @foreach($establecimientos as $establecimiento)
                        <flux:select.option value="{{ $establecimiento->id }}">{{ $establecimiento->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="usuarioId" label="Usuario" required>
                    <flux:select.option value="0">Selecciona un usuario del tenant</flux:select.option>
                    @foreach($usuarios as $usuario)
                        <flux:select.option value="{{ $usuario->id }}">{{ $usuario->name }} · {{ $usuario->email }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:button type="submit" variant="primary">Asignar acceso</flux:button>
            </form>
        </section>
    @endif

    @if(auth()->user()->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())
        <form wire:submit="crearUbicacion" class="max-w-2xl space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Administrar ubicaciones autorizadas</flux:heading>
            <flux:select wire:model="almacenSeleccionado" label="Almacén asignado" required>
                <flux:select.option value="0">Selecciona un almacén</flux:select.option>
                @foreach($establecimientos as $establecimiento)
                    @foreach($establecimiento->almacenes as $almacen)
                        <flux:select.option value="{{ $almacen->id }}">{{ $establecimiento->nombre }} · {{ $almacen->nombre }}</flux:select.option>
                    @endforeach
                @endforeach
            </flux:select>
            <flux:input wire:model="nombreUbicacion" label="Nombre de la ubicación" required />
            <flux:input wire:model="codigoUbicacion" label="Código (opcional)" />
            <flux:button type="submit" variant="primary">Crear ubicación</flux:button>
        </form>
    @endif

    <section class="space-y-4">
        <flux:heading size="lg">Estructura de la empresa</flux:heading>
        @forelse($establecimientos as $establecimiento)
            <article class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div><h2 class="font-semibold">{{ $establecimiento->nombre }}</h2><p class="text-sm text-zinc-500">{{ $establecimiento->codigo }} · {{ str_replace('_', ' ', $establecimiento->tipo) }}</p></div>
                    <span class="text-sm">{{ $establecimiento->activo ? 'Activo' : 'Inactivo' }}</span>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse($establecimiento->almacenes as $almacen)
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                            <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-medium">{{ $almacen->nombre }} <span class="text-xs text-zinc-500">{{ $almacen->codigo }}</span></h3><div class="flex gap-3 text-sm"><a class="text-blue-700 underline dark:text-blue-300" href="{{ route('inventario.show', [$establecimiento->id, $almacen->id]) }}">Inventario</a>@if(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'ventas'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists())<a class="text-blue-700 underline dark:text-blue-300" href="{{ route('ventas.index', [$establecimiento->id, $almacen->id]) }}">Ventas</a>@endif</div></div>
                            <p class="mt-2 text-sm text-zinc-500">{{ $almacen->ubicaciones->pluck('nombre')->join(', ') ?: 'Sin ubicaciones' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">Este establecimiento todavía no tiene almacenes.</p>
                    @endforelse
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed p-8 text-center text-zinc-500">Aún no hay establecimientos registrados.</p>
        @endforelse
    </section>
</div>

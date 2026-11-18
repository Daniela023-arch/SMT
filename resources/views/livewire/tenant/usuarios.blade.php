<div class="space-y-7">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Equipo del tenant</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white sm:text-3xl">Personal y accesos</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Administra quién entra a cada sucursal y almacén. Los permisos nunca cruzan a otra ferretería.</p>
        </div>
        @if ($esAdmin)
            <button type="button" wire:click="alternarGestionPersonal" class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <span class="grid size-9 place-items-center rounded-lg {{ $personalHabilitado ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $personalHabilitado ? '✓' : '—' }}</span>
                <span class="text-left"><span class="block">{{ $personalHabilitado ? 'Personal habilitado' : 'Operas solo' }}</span><span class="mt-0.5 block text-xs font-normal text-slate-500">{{ $personalHabilitado ? 'Pulsa para deshabilitar' : 'Pulsa para agregar personal' }}</span></span>
            </button>
        @endif
    </header>

    @error('usuario') <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">{{ $message }}</div> @enderror
    @error('personal') <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">{{ $message }}</div> @enderror

    @if ($enlaceInicial)
        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950">
            <h2 class="font-semibold text-blue-950 dark:text-blue-100">Enlace seguro para {{ $emailEnlace }}</h2>
            <p class="mt-1 text-sm text-blue-900 dark:text-blue-200">Comparte este enlace con la persona para que establezca su contraseña. Por seguridad se muestra solo en esta sesión.</p>
            <a href="{{ $enlaceInicial }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex max-w-full break-all rounded-lg bg-white px-3 py-2 text-sm font-semibold text-blue-800 underline dark:bg-slate-900 dark:text-blue-200">{{ $enlaceInicial }}</a>
            <button type="button" wire:click="$set('enlaceInicial', null)" class="ml-3 text-xs font-semibold text-blue-800 underline dark:text-blue-200">Cerrar</button>
        </section>
    @endif

    @if ($personalHabilitado)
        <section class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="font-bold text-slate-950 dark:text-white">Usuarios del equipo</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $usuarios->count() }} usuario(s) dentro de tu alcance.</p></div>
                <button type="button" wire:click="abrirFormulario" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800"><span aria-hidden="true">＋</span> Agregar usuario</button>
            </div>

            @if ($mostrarFormulario)
                <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true" aria-labelledby="formulario-usuario-title" wire:click.self="cancelarEdicion" wire:keydown.escape="cancelarEdicion">
                    <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <form wire:submit="guardar" class="space-y-5 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div><h2 id="formulario-usuario-title" class="text-lg font-bold text-slate-950 dark:text-white">{{ $usuarioSeleccionado ? 'Editar usuario' : 'Agregar usuario' }}</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $usuarioSeleccionado ? 'Actualiza su rol, sucursales o almacenes permitidos.' : 'Define el acceso y las asignaciones de la nueva persona.' }}</p></div>
                    <button type="button" wire:click="cancelarEdicion" class="grid size-9 shrink-0 place-items-center rounded-lg border border-slate-300 text-slate-500 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Cerrar formulario">✕</button>
                </div>

                <flux:input wire:model="nombre" label="Nombre completo" autocomplete="name" />
                @error('nombre') <p class="-mt-4 text-xs text-rose-600">{{ $message }}</p> @enderror
                <flux:input wire:model="email" type="email" label="Correo electrónico" autocomplete="email" />
                @error('email') <p class="-mt-4 text-xs text-rose-600">{{ $message }}</p> @enderror

                <flux:select wire:model.live="rol" label="Rol" required>
                    @if ($esAdmin)<flux:select.option value="gerente">Gerente</flux:select.option>@endif
                    <flux:select.option value="ventas">Encargado de ventas</flux:select.option>
                    <flux:select.option value="almacen">Encargado de almacén</flux:select.option>
                </flux:select>
                @error('rol') <p class="-mt-4 text-xs text-rose-600">{{ $message }}</p> @enderror

                @if ($rol === 'gerente' && $esAdmin)
                    <fieldset class="space-y-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                        <legend class="px-1 text-sm font-semibold text-slate-900 dark:text-white">Accesos operativos del gerente</legend>
                        <p class="text-xs text-slate-600 dark:text-slate-300">El gerente solo verá los módulos seleccionados, dentro de sus sucursales asignadas.</p>
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg bg-white p-3 text-sm dark:bg-slate-900"><input type="checkbox" wire:model="puedeVerPuntoVenta" class="mt-0.5 rounded border-slate-300 text-blue-700 focus:ring-blue-600"><span><span class="block font-semibold text-slate-900 dark:text-white">Punto de venta</span><span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Puede abrir cajas y gestionar ventas en sus sucursales.</span></span></label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg bg-white p-3 text-sm dark:bg-slate-900"><input type="checkbox" wire:model="puedeVerAlmacen" class="mt-0.5 rounded border-slate-300 text-blue-700 focus:ring-blue-600"><span><span class="block font-semibold text-slate-900 dark:text-white">Almacén e inventario</span><span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Puede consultar y operar los almacenes de sus sucursales.</span></span></label>
                    </fieldset>
                @endif

                @if ($rol !== 'ventas')
                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium text-slate-800 dark:text-slate-200">Sucursales permitidas</legend>
                    <p class="text-xs text-slate-500">El gerente y el personal solo verán las sucursales que marques.</p>
                    <div class="grid gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700 sm:grid-cols-2">
                        @forelse ($branchOptions as $branch)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                <input type="checkbox" wire:model.live="establecimientos" value="{{ $branch->id }}" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                                <span>{{ $branch->nombre }}</span>
                            </label>
                        @empty
                            <p class="col-span-2 text-sm text-slate-500">No hay sucursales disponibles.</p>
                        @endforelse
                    </div>
                    @error('establecimientos') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </fieldset>
                @endif

                @if ($rol === 'ventas')
                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium text-slate-800 dark:text-slate-200">Punto de venta asignado</legend>
                        <p class="text-xs text-slate-500">El encargado solo podrá entrar a esta caja y a las ventas de este almacén.</p>
                        <select wire:model="almacenVentaId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Selecciona un punto de venta</option>
                            @foreach ($warehouseOptions as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->establecimiento?->nombre }} · {{ $warehouse->nombre }}</option>
                            @endforeach
                        </select>
                        @error('almacenes') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('almacenVentaId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </fieldset>
                @elseif ($rol === 'almacen')
                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium text-slate-800 dark:text-slate-200">Almacenes permitidos</legend>
                        <p class="text-xs text-slate-500">Puede tener varios almacenes asignados; no podrá operar en otros.</p>
                        <div class="grid gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700 sm:grid-cols-2">
                            @forelse ($warehouseOptions as $warehouse)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                    <input type="checkbox" wire:model="almacenes" value="{{ $warehouse->id }}" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                                    <span>{{ $warehouse->nombre }} · {{ $branchOptions->firstWhere('id', $warehouse->establecimiento_id)?->nombre }}</span>
                                </label>
                            @empty
                                <p class="col-span-2 text-sm text-slate-500">Selecciona primero una sucursal con almacenes activos.</p>
                            @endforelse
                        </div>
                        @error('almacenes') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </fieldset>
                @endif

                @if ($usuarioSeleccionado)
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model="activo" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600"> Usuario activo</label>
                @endif
                <div class="flex flex-wrap gap-3">
                    <flux:button type="submit" variant="primary">{{ $usuarioSeleccionado ? 'Guardar cambios' : 'Agregar al equipo' }}</flux:button>
                    @if ($usuarioSeleccionado)<flux:button type="button" variant="ghost" wire:click="cancelarEdicion">Cancelar</flux:button>@endif
                </div>
            </form>
                    </div>
                </div>
            @endif

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                    <div><h2 class="font-bold text-slate-950 dark:text-white">Usuarios del equipo</h2><p class="mt-1 text-xs text-slate-500">{{ $usuarios->count() }} usuario(s) dentro de tu alcance.</p></div>
                    <div class="flex gap-2"><input wire:model.live.debounce.300ms="buscar" type="search" placeholder="Buscar nombre o correo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 sm:w-48"><select wire:model.live="filtroRol" class="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="todos">Todos los roles</option><option value="gerente">Gerentes</option><option value="ventas">Ventas</option><option value="almacen">Almacén</option></select></div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($usuarios as $usuario)
                        @php $rolUsuario = $usuario->roles->first()?->slug; @endphp
                        <article class="space-y-3 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0"><h3 class="truncate font-semibold text-slate-950 dark:text-white">{{ $usuario->name }}</h3><p class="truncate text-sm text-slate-600 dark:text-slate-300">{{ $usuario->email }}</p></div>
                                <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ ['admin' => 'Administrador', 'gerente' => 'Gerente', 'ventas' => 'Ventas', 'almacen' => 'Almacén'][$rolUsuario] ?? 'Personal' }}</span><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' => $usuario->activo, 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! $usuario->activo])>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></div>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span>Sucursales: {{ $usuario->establecimientos->pluck('nombre')->join(', ') ?: 'Sin asignación' }}</span>
                                @if (in_array($rolUsuario, ['almacen', 'ventas'], true))<span>{{ $rolUsuario === 'ventas' ? 'Punto de venta' : 'Almacenes' }}: {{ $usuario->almacenesAsignados->pluck('nombre')->join(', ') ?: 'Sin asignación' }}</span>@endif
                                @if ($rolUsuario === 'gerente')<span>Accesos: {{ $usuario->puede_ver_punto_venta ? 'Punto de venta' : '' }}{{ $usuario->puede_ver_punto_venta && $usuario->puede_ver_almacen ? ' · ' : '' }}{{ $usuario->puede_ver_almacen ? 'Almacén e inventario' : '' }}{{ ! $usuario->puede_ver_punto_venta && ! $usuario->puede_ver_almacen ? 'Sin acceso a punto de venta ni almacén' : '' }}</span>@endif
                            </div>
                            @if ($rolUsuario !== 'admin')
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="editar({{ $usuario->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Editar / reasignar</button>
                                    <button type="button" wire:click="alternarActivo({{ $usuario->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ $usuario->activo ? 'Desactivar' : 'Activar' }}</button>
                                    <button type="button" wire:click="restablecer({{ $usuario->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Restablecer contraseña</button>
                                </div>
                            @endif
                            @if ($usuarioEnlaceId === (int) $usuario->id && $enlaceRestablecimiento)
                                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/50">
                                    <p class="text-sm font-semibold text-blue-950 dark:text-blue-100">Enlace para restablecer la contraseña de {{ $usuario->email }}</p>
                                    <p class="mt-1 text-xs text-blue-900 dark:text-blue-200">Ábrelo para establecer una contraseña nueva o compártelo con la persona.</p>
                                    <a href="{{ $enlaceRestablecimiento }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-xs font-semibold text-blue-800 underline dark:text-blue-200">{{ $enlaceRestablecimiento }}</a>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="p-10 text-center"><p class="font-semibold text-slate-800 dark:text-slate-100">No hay personal en este alcance</p><p class="mt-1 text-sm text-slate-500">Agrega un usuario y asigna sus sucursales o almacenes.</p></div>
                    @endforelse
                </div>
            </section>
        </section>
    @else
        <section class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto grid size-12 place-items-center rounded-full bg-slate-100 text-xl dark:bg-slate-800">👤</div>
            <h2 class="mt-4 font-bold text-slate-950 dark:text-white">Tu ferretería está configurada para operar sin personal</h2>
            <p class="mx-auto mt-2 max-w-lg text-sm text-slate-600 dark:text-slate-300">Activa la gestión de personal cuando quieras agregar un gerente o encargados de ventas y almacén.</p>
        </section>
    @endif
</div>

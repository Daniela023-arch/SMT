<div class="space-y-8">
    <div>
        <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-700">Supervisión de plataforma</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Supervisión global</h1>
        <p class="mt-2 text-sm text-slate-600">Consulta las personas, sucursales y movimientos de cada tenant desde una sola pantalla.</p>
    </div>

    <section class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <label class="text-sm font-medium text-slate-700">Tenant
            <select wire:model.live="tenantId" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">Todos los tenants</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->nombre }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-medium text-slate-700">Desde<input wire:model.live="desde" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></label>
        <label class="text-sm font-medium text-slate-700">Hasta<input wire:model.live="hasta" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></label>
    </section>

    <section class="space-y-3">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><h2 class="text-xl font-bold text-slate-900">Personas del tenant</h2><p class="text-sm text-slate-500">Consulta sus roles, estado y asignaciones. No se modifican cuentas desde esta vista.</p></div>
            <input wire:model.live.debounce.300ms="buscarUsuario" type="search" placeholder="Buscar nombre o correo" class="w-full rounded-lg border-slate-300 text-sm sm:max-w-xs">
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Persona</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3">Sucursales</th><th class="px-4 py-3">Almacenes asignados</th><th class="px-4 py-3">Cuenta</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $usuario)
                        @php $rolTenant = $usuario->roles->first(fn ($rol) => (int) $rol->tenant_id === (int) $usuario->tenant_id); @endphp
                        <tr>
                            <td class="px-4 py-3"><span class="block font-semibold text-slate-900">{{ $usuario->name }}</span><span class="text-xs text-slate-500">{{ $usuario->email }}</span></td>
                            <td class="px-4 py-3 font-medium">{{ $usuario->tenant?->nombre ?? 'Tenant eliminado' }}</td>
                            <td class="px-4 py-3">{{ $rolTenant?->nombre ?? 'Sin rol' }}</td>
                            <td class="max-w-56 px-4 py-3 text-slate-600">{{ $usuario->establecimientos->pluck('nombre')->join(', ') ?: 'Sin asignación' }}</td>
                            <td class="max-w-56 px-4 py-3 text-slate-600">{{ $usuario->almacenesAsignados->pluck('nombre')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-800' => $usuario->activo, 'bg-slate-100 text-slate-600' => ! $usuario->activo])>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay personas para el tenant o la búsqueda seleccionada.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="border-t border-slate-100 p-4">{{ $usuarios->links() }}</div>
        </div>
    </section>

    <section class="space-y-3">
        <div><h2 class="text-xl font-bold text-slate-900">Registro administrativo</h2><p class="text-sm text-slate-500">Cambios de configuración y acciones registradas en la bitácora.</p></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="divide-y divide-slate-100">
                @forelse($auditorias as $evento)
                    <article class="grid gap-2 px-5 py-4 sm:grid-cols-[1fr_auto] sm:items-center"><div><p class="font-semibold text-slate-900">{{ str_replace('_', ' ', $evento->accion) }} <span class="font-normal text-slate-500">· {{ $evento->entidad ?? 'Evento' }}</span></p><p class="mt-1 text-xs text-slate-500">{{ $evento->tenant?->nombre ?? 'Plataforma' }} · {{ $evento->usuario?->name ?? 'Sistema' }}</p></div><time class="text-xs text-slate-500">{{ $evento->created_at?->format('d/m/Y H:i') }}</time></article>
                @empty<p class="p-6 text-center text-sm text-slate-500">Sin eventos para los filtros seleccionados.</p>@endforelse
            </div>
            <div class="border-t border-slate-100 p-4">{{ $auditorias->links() }}</div>
        </div>
    </section>

    <section class="space-y-3">
        <div><h2 class="text-xl font-bold text-slate-900">Movimientos de inventario</h2><p class="text-sm text-slate-500">Entradas, salidas, ajustes y transferencias registradas.</p></div>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Movimiento</th><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Almacén</th><th class="px-4 py-3">Usuario</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($movimientos as $movimiento)<tr><td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-3 font-medium">{{ $movimiento->tenant?->nombre }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $movimiento->tipo) }} · {{ $movimiento->cantidad }}</td><td class="px-4 py-3">{{ $movimiento->producto?->sku }} · {{ $movimiento->producto?->nombre }}</td><td class="px-4 py-3">{{ $movimiento->almacenOrigen?->nombre ?? $movimiento->almacenDestino?->nombre }}</td><td class="px-4 py-3">{{ $movimiento->usuario?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Sin movimientos para los filtros seleccionados.</td></tr>@endforelse
        </tbody></table><div class="border-t border-slate-100 p-4">{{ $movimientos->links() }}</div></div>
    </section>

    <section class="space-y-3">
        <div><h2 class="text-xl font-bold text-slate-900">Ventas</h2><p class="text-sm text-slate-500">Consulta global de ventas sin controles para modificarlas.</p></div>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="w-full min-w-[700px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Folio</th><th class="px-4 py-3">Sucursal / almacén</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Estado</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($ventas as $venta)<tr><td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $venta->created_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-3 font-medium">{{ $venta->tenant?->nombre }}</td><td class="px-4 py-3">{{ $venta->folio }}</td><td class="px-4 py-3">{{ $venta->establecimiento?->nombre }} · {{ $venta->almacen?->nombre }}</td><td class="px-4 py-3">{{ $venta->usuario?->name }}</td><td class="px-4 py-3 text-right">${{ number_format((float) $venta->total, 2) }}</td><td class="px-4 py-3">{{ $venta->estado }}</td></tr>@empty<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Sin ventas para los filtros seleccionados.</td></tr>@endforelse
        </tbody></table><div class="border-t border-slate-100 p-4">{{ $ventas->links() }}</div></div>
    </section>

    <section class="space-y-3">
        <div><h2 class="text-xl font-bold text-slate-900">Transferencias entre almacenes</h2><p class="text-sm text-slate-500">Seguimiento global de solicitudes, autorizaciones y recepciones.</p></div>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Folio</th><th class="px-4 py-3">Ruta</th><th class="px-4 py-3">Solicitante</th><th class="px-4 py-3">Estado</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($transferencias as $transferencia)<tr><td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $transferencia->created_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-3 font-medium">{{ $transferencia->tenant?->nombre }}</td><td class="px-4 py-3">{{ $transferencia->folio }}</td><td class="px-4 py-3">{{ $transferencia->almacenOrigen?->nombre }} → {{ $transferencia->almacenDestino?->nombre }}</td><td class="px-4 py-3">{{ $transferencia->solicitante?->name }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $transferencia->estado) }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Sin transferencias para los filtros seleccionados.</td></tr>@endforelse
        </tbody></table><div class="border-t border-slate-100 p-4">{{ $transferencias->links() }}</div></div>
    </section>
</div>

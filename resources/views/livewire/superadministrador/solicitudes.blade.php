<div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Admisión de nuevos clientes</p><h1 class="mt-1 text-3xl font-extrabold tracking-tight">Solicitudes de contratación</h1><p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Revisa cada solicitud y aprovisiona su espacio de trabajo al aprobarla.</p></div>
        <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">{{ $pendientes }} pendientes</span>
    </header>

    @if($passwordTemporal && $credencialesEmail)
        <section role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-100">
            <h2 class="font-bold">Tenant creado y acceso preparado</h2>
            <p class="mt-1 text-sm">Entrega estas credenciales iniciales al administrador. La contraseña solo se muestra en esta pantalla y se debe cambiar después del primer acceso.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2"><div class="rounded-xl bg-white/70 p-3 text-sm dark:bg-black/20"><span class="block text-xs opacity-70">Correo</span><strong>{{ $credencialesEmail }}</strong></div><div class="rounded-xl bg-white/70 p-3 text-sm dark:bg-black/20"><span class="block text-xs opacity-70">Contraseña temporal</span><strong class="font-mono">{{ $passwordTemporal }}</strong></div></div>
            <button type="button" wire:click="$set('passwordTemporal', null)" class="mt-3 text-xs font-bold underline underline-offset-4">Ocultar credenciales</button>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap gap-3 border-b border-slate-100 p-4 dark:border-zinc-800">
                <input wire:model.live.debounce.300ms="buscar" class="min-w-[220px] flex-1 rounded-xl border-slate-300 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Buscar negocio o correo">
                <select wire:model.live="estadoFiltro" class="rounded-xl border-slate-300 text-sm dark:border-zinc-700 dark:bg-zinc-950"><option value="pendiente">Pendientes</option><option value="aprobada">Aprobadas</option><option value="rechazada">Rechazadas</option><option value="todas">Todas</option></select>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                @forelse($solicitudes as $solicitud)
                    <button type="button" wire:click="seleccionar({{ $solicitud->id }})" @class(['flex w-full items-center justify-between gap-4 p-4 text-left transition hover:bg-indigo-50/70 dark:hover:bg-indigo-950/20 sm:px-5', 'bg-indigo-50 dark:bg-indigo-950/30' => $solicitudSeleccionada === $solicitud->id])>
                        <span class="min-w-0"><strong class="block truncate text-sm">{{ $solicitud->nombre_negocio }}</strong><span class="mt-1 block truncate text-xs text-slate-500 dark:text-zinc-400">{{ $solicitud->email_contacto }} · {{ $solicitud->tipoInventario?->nombre }}</span></span>
                        <span class="shrink-0 text-right"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $solicitud->estado === 'pendiente', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $solicitud->estado === 'aprobada', 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300' => $solicitud->estado === 'rechazada'])>{{ ucfirst($solicitud->estado) }}</span><span class="mt-1 block text-[11px] text-slate-500">{{ $solicitud->created_at?->format('d/m/Y') }}</span></span>
                    </button>
                @empty
                    <p class="p-10 text-center text-sm text-slate-500 dark:text-zinc-400">No hay solicitudes con estos filtros.</p>
                @endforelse
            </div>
            <div class="border-t border-slate-100 p-4 dark:border-zinc-800">{{ $solicitudes->links() }}</div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            @if($solicitudActual)
                <h2 class="text-lg font-bold">{{ $solicitudActual->nombre_negocio }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">{{ $solicitudActual->email_contacto }} · {{ $solicitudActual->tipoInventario?->nombre }}</p>
                <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300"><p class="font-semibold text-slate-800 dark:text-white">Información de la solicitud</p><p class="mt-2 whitespace-pre-line">{{ $solicitudActual->observaciones ?: 'Sin comentarios adicionales.' }}</p></div>
                @if($solicitudActual->estado === 'pendiente')
                    <div class="mt-5 space-y-4 border-t border-slate-100 pt-5 dark:border-zinc-800">
                        <h3 class="font-bold">Aprobar y configurar acceso</h3>
                        <label class="grid gap-1.5 text-sm font-semibold">Administrador inicial<input wire:model="adminNombre" class="rounded-xl border-slate-300 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nombre de la persona responsable">@error('adminNombre')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="grid gap-1.5 text-sm font-semibold">Plan<select wire:model="planId" class="rounded-xl border-slate-300 text-sm dark:border-zinc-700 dark:bg-zinc-950"><option value="">Selecciona un plan activo</option>@foreach($planes as $plan)<option value="{{ $plan->id }}">{{ $plan->nombre }} · ${{ number_format((float) $plan->precio, 2) }} MXN / {{ $plan->periodicidad }}</option>@endforeach</select>@error('planId')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <fieldset><legend class="text-sm font-semibold">Módulos autorizados</legend><div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($modulosDisponibles as $modulo)<label class="flex items-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs dark:border-zinc-700"><input type="checkbox" wire:model="modulos" value="{{ $modulo->clave }}" class="rounded border-slate-300 text-indigo-700">{{ $modulo->nombre }}</label>@endforeach</div>@error('modulos')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</fieldset>
                        <button type="button" wire:click="aprobar" wire:confirm="¿Aprobar y crear el tenant con este plan, módulos y administrador?" class="w-full rounded-xl bg-indigo-700 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-800 disabled:opacity-50" @disabled($planes->isEmpty() || $modulosDisponibles->isEmpty())>Aprobar y crear tenant</button>
                        @if($planes->isEmpty())<p class="text-xs text-amber-700">Crea al menos un plan antes de aprobar solicitudes.</p>@endif
                        @if($modulosDisponibles->isEmpty())<p class="text-xs text-amber-700">Activa módulos en el catálogo antes de aprobar solicitudes.</p>@endif
                        <div class="space-y-2 border-t border-slate-100 pt-4 dark:border-zinc-800"><label class="grid gap-1.5 text-sm font-semibold">Motivo de rechazo<textarea wire:model="motivoRechazo" rows="2" class="rounded-xl border-slate-300 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>@error('motivoRechazo')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label><button type="button" wire:click="rechazar" wire:confirm="¿Marcar esta solicitud como rechazada?" class="w-full rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300">Rechazar solicitud</button></div>
                    </div>
                @else
                    <p class="mt-5 rounded-xl bg-slate-50 p-4 text-sm dark:bg-zinc-800">Esta solicitud ya fue {{ $solicitudActual->estado }}.</p>
                @endif
            @else
                <div class="grid min-h-64 place-items-center text-center"><div><span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-xl text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">↗</span><h2 class="mt-4 font-bold">Selecciona una solicitud</h2><p class="mt-1 max-w-xs text-sm text-slate-500 dark:text-zinc-400">Aquí revisarás los datos y decidirás si se aprueba el acceso a MULTIVERSE.</p></div></div>
            @endif
        </section>
    </div>
</div>

<div class="space-y-6">
    <header class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Dashboard global</h1>
            <p class="mt-1 text-sm text-slate-500">Resumen de tenants, solicitudes y suscripciones de MULTIVERSE.</p>
        </div>
        <a href="{{ route('superadmin.solicitudes') }}" wire:navigate class="inline-flex min-h-10 items-center justify-center gap-2 self-start rounded-xl bg-blue-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 md:self-auto">
            <span class="text-lg leading-none">＋</span> Revisar solicitudes
            @if($solicitudesPendientes > 0)<span class="rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $solicitudesPendientes }}</span>@endif
        </a>
    </header>

    <section aria-label="Resumen de la plataforma" class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        @foreach ([
            ['Tenants activos', number_format($tenantsActivos), 'registrados en la plataforma', '▣', 'bg-blue-50 text-blue-800'],
            ['Solicitudes pendientes', number_format($solicitudesPendientes), 'requieren revisión', '◷', 'bg-amber-50 text-amber-700'],
            ['Suscripciones activas', number_format($suscripcionesActivas), $planesActivos.' planes disponibles', '✓', 'bg-emerald-50 text-emerald-700'],
            ['Próximas a vencer', number_format($porVencer), 'en los siguientes 15 días', '!', 'bg-rose-50 text-rose-700'],
            ['Usuarios registrados', number_format($usuariosRegistrados), 'en todos los tenants', '♙', 'bg-sky-50 text-sky-700'],
            ['Ingresos (mes)', '$'.number_format($ingresosMes, 0).' MXN', 'pagos confirmados', '$', 'bg-emerald-50 text-emerald-700'],
        ] as [$label, $value, $hint, $icon, $tone])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center justify-between gap-2"><span class="text-sm text-slate-500">{{ $label }}</span><span class="grid size-9 shrink-0 place-items-center rounded-xl text-lg font-bold {{ $tone }}">{{ $icon }}</span></div>
                <div class="mt-3 truncate text-2xl font-bold tracking-tight text-slate-900" title="{{ $value }}">{{ $value }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ $hint }}</div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 sm:p-6">
            <div class="mb-3"><h2 class="font-semibold text-slate-900">Crecimiento de tenants</h2><p class="mt-1 text-xs text-slate-500">Total acumulado durante los últimos seis meses</p></div>
            @php
                $maxCrecimiento = max(1, (int) $crecimiento->max('total'));
                $puntosCrecimiento = $crecimiento->values()->map(fn ($mes, $indice) => (20 + ($indice * 560 / max(1, $crecimiento->count() - 1))).','.((160 - (($mes['total'] / $maxCrecimiento) * 125))))->implode(' ');
                $areaCrecimiento = 'M 20 160 L '.str_replace(' ', ' L ', $puntosCrecimiento).' L 580 160 Z';
            @endphp
            <div class="h-64 w-full">
                <svg viewBox="0 0 600 200" class="h-full w-full overflow-visible" role="img" aria-label="Gráfica de crecimiento mensual de tenants">
                    <defs><linearGradient id="growthFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#334fba" stop-opacity=".20"/><stop offset="100%" stop-color="#334fba" stop-opacity=".01"/></linearGradient></defs>
                    @foreach([35, 75, 115, 155] as $gridY)<line x1="20" y1="{{ $gridY }}" x2="580" y2="{{ $gridY }}" stroke="#e8ebf1" stroke-dasharray="4 5"/>@endforeach
                    <path d="{{ $areaCrecimiento }}" fill="url(#growthFill)"/>
                    <polyline points="{{ $puntosCrecimiento }}" fill="none" stroke="#334fba" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($crecimiento->values() as $indice => $mes)
                        @php
                            $x = 20 + ($indice * 560 / max(1, $crecimiento->count() - 1));
                            $y = 160 - (($mes['total'] / $maxCrecimiento) * 125);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="white" stroke="#334fba" stroke-width="2.5"><title>{{ $mes['mes'] }}: {{ $mes['total'] }} tenants</title></circle>
                        <text x="{{ $x }}" y="190" text-anchor="middle" font-size="11" fill="#737b8c">{{ $mes['mes'] }}</text>
                    @endforeach
                </svg>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div><h2 class="font-semibold text-slate-900">Tenants por tipo</h2><p class="mt-1 text-xs text-slate-500">Distribución de los negocios registrados</p></div>
            @if($tenantsPorTipo->isNotEmpty())
                @php
                    $totalTipos = max(1, (int) $tenantsPorTipo->sum('total'));
                    $coloresTipos = ['#334fba', '#58a9c9', '#e6b956', '#43a379', '#dc6868'];
                    $anguloActual = 0;
                    $tramosDonut = [];
                    foreach ($tenantsPorTipo as $indiceTipo => $tipo) {
                        $anguloFinal = $anguloActual + ($tipo->total / $totalTipos * 360);
                        $colorTipo = $coloresTipos[$indiceTipo % count($coloresTipos)];
                        $tramosDonut[] = $colorTipo.' '.$anguloActual.'deg '.$anguloFinal.'deg';
                        $anguloActual = $anguloFinal;
                    }
                    $fondoDonut = 'conic-gradient('.implode(', ', $tramosDonut).')';
                @endphp
                <div class="mt-6 flex flex-col items-center gap-5 sm:flex-row sm:justify-around">
                    <div class="relative size-36 shrink-0 rounded-full" style="background: {{ $fondoDonut }}"><div class="absolute inset-[22px] grid place-items-center rounded-full bg-white text-center"><span><strong class="block text-2xl text-slate-900">{{ number_format($totalTipos) }}</strong><span class="text-[10px] text-slate-500">tenants</span></span></div></div>
                    <div class="grid w-full gap-x-3 gap-y-2 sm:w-auto">
                        @foreach($tenantsPorTipo as $indiceTipo => $tipo)
                            <div class="flex items-center gap-2 text-xs"><span class="size-2 shrink-0 rounded-full" style="background: {{ $coloresTipos[$indiceTipo % count($coloresTipos)] }}"></span><span class="min-w-0 flex-1 truncate text-slate-600">{{ $tipo->nombre }}</span><strong class="text-slate-800">{{ $tipo->total }}</strong></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-5 grid h-48 place-items-center rounded-xl bg-slate-50 text-center"><p class="text-sm text-slate-500">Aún no hay tenants registrados.</p></div>
            @endif
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 sm:p-6">
            <div class="mb-3 flex items-start justify-between gap-3"><div><h2 class="font-semibold text-slate-900">Ingresos por suscripciones</h2><p class="mt-1 text-xs text-slate-500">Pagos confirmados · MXN</p></div><span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Este mes: ${{ number_format($ingresosMes, 2) }}</span></div>
            @php
                $maxIngreso = max(1, (float) $ingresosPorMes->max('total'));
            @endphp
            <div class="mt-5 grid h-52 grid-cols-6 items-end gap-4 sm:gap-7">
                @foreach($ingresosPorMes as $mes)
                    @php
                        $alto = $mes['total'] > 0 ? max(6, (int) round(($mes['total'] / $maxIngreso) * 100)) : 2;
                    @endphp
                    <div class="flex h-full flex-col items-center justify-end gap-2"><span class="text-[10px] font-medium text-slate-500">{{ $mes['total'] > 0 ? '$'.number_format($mes['total'], 0) : '—' }}</span><div class="flex h-[72%] w-full items-end justify-center"><div class="w-full max-w-12 rounded-t-md bg-sky-500 transition-all" style="height: {{ $alto }}%"></div></div><span class="text-[11px] text-slate-500">{{ $mes['mes'] }}</span></div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div><h2 class="font-semibold text-slate-900">Alertas administrativas</h2><p class="mt-1 text-xs text-slate-500">Elementos que requieren seguimiento</p></div>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse($suscripcionesPorVencer as $suscripcion)
                    <li class="flex gap-3 rounded-xl bg-amber-50 p-3 text-amber-950"><span class="mt-0.5 text-amber-700">!</span><span class="min-w-0"><strong class="block truncate text-xs">{{ $suscripcion->tenant?->nombre ?? 'Tenant' }}</strong><span class="text-[11px] text-amber-800">Vence el {{ $suscripcion->fin_at?->format('d/m/Y') }}</span></span></li>
                @empty
                    <li class="flex gap-3 rounded-xl bg-emerald-50 p-3 text-emerald-950"><span class="text-emerald-700">✓</span><span class="text-xs">No hay suscripciones próximas a vencer.</span></li>
                @endforelse
                @if($pagosRechazados > 0)<li class="flex gap-3 rounded-xl bg-rose-50 p-3 text-rose-950"><span class="text-rose-700">!</span><span class="text-xs"><strong>{{ $pagosRechazados }} pagos</strong> rechazados por el proveedor.</span></li>@endif
                @if($solicitudesPendientes > 0)<li class="flex gap-3 rounded-xl bg-blue-50 p-3 text-blue-950"><span class="text-blue-700">↗</span><span class="text-xs"><strong>{{ $solicitudesPendientes }} solicitudes</strong> esperan revisión.</span></li>@endif
            </ul>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6"><div><h2 class="font-semibold text-slate-900">Solicitudes pendientes</h2><p class="mt-1 text-xs text-slate-500">Solicitudes recientes recibidas desde el sitio público</p></div><a href="{{ route('superadmin.solicitudes') }}" wire:navigate class="rounded-lg px-3 py-2 text-xs font-semibold text-blue-800 transition hover:bg-blue-50">Ver todas →</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 font-semibold sm:px-6">Negocio</th><th class="px-4 py-3 font-semibold">Tipo</th><th class="px-4 py-3 font-semibold">Estado</th><th class="px-5 py-3 font-semibold sm:px-6">Fecha</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($solicitudes as $solicitud)
                    <tr class="transition hover:bg-slate-50"><td class="px-5 py-3.5 sm:px-6"><p class="font-semibold text-slate-900">{{ $solicitud->nombre_negocio }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $solicitud->email_contacto }}</p></td><td class="px-4 py-3.5 text-slate-600">{{ $solicitud->tipoInventario?->nombre ?? '—' }}</td><td class="px-4 py-3.5"><span @class(['inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold', 'bg-amber-50 text-amber-700' => $solicitud->estado === 'pendiente', 'bg-emerald-50 text-emerald-700' => $solicitud->estado === 'aprobada', 'bg-slate-100 text-slate-600' => ! in_array($solicitud->estado, ['pendiente', 'aprobada'], true)])>{{ ucfirst($solicitud->estado) }}</span></td><td class="px-5 py-3.5 text-xs text-slate-500 sm:px-6">{{ $solicitud->created_at?->format('d/m/Y') }}</td></tr>
                @empty<tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">Todavía no hay solicitudes.</td></tr>@endforelse
            </tbody></table></div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-2"><div><h2 class="font-semibold text-slate-900">Actividad reciente</h2><p class="mt-1 text-xs text-slate-500">Registro administrativo de la plataforma</p></div><a href="{{ route('superadmin.reportes') }}" wire:navigate class="text-xs font-semibold text-blue-800">Ver reportes</a></div>
            <ul class="mt-5 space-y-4">
                @forelse($actividad as $evento)
                    <li class="flex gap-3 text-sm"><span class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-700"></span><span class="min-w-0"><strong class="block truncate text-xs text-slate-800">{{ str($evento->accion)->replace('_', ' ')->ucfirst() }}</strong><span class="mt-0.5 block truncate text-[11px] text-slate-500">{{ $evento->usuario?->name ?? 'Sistema' }}@if($evento->tenant) · {{ $evento->tenant->nombre }}@endif</span><span class="mt-0.5 block text-[10px] text-slate-400">{{ $evento->created_at?->diffForHumans() }}</span></span></li>
                @empty
                    <li class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">No hay actividad administrativa reciente.</li>
                @endforelse
            </ul>
        </article>
    </section>
</div>

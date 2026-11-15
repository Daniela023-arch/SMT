@props(['title' => 'Panel global'])

<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
    <style>
        html.dark body.superadmin-shell { background:#0b1120 !important; color:#e2e8f0 !important; }
        html.dark body.superadmin-shell header,
        html.dark body.superadmin-shell [class~="bg-white"] { background-color:#111c2e !important; }
        html.dark body.superadmin-shell header { border-color:#293548 !important; }
        html.dark body.superadmin-shell [class~="bg-slate-50"],
        html.dark body.superadmin-shell [class~="bg-slate-100"] { background-color:#172235 !important; }
        html.dark body.superadmin-shell [class~="bg-blue-50"] { background-color:#172b4d !important; }
        html.dark body.superadmin-shell [class~="bg-emerald-50"] { background-color:#123329 !important; }
        html.dark body.superadmin-shell [class~="bg-amber-50"] { background-color:#392d17 !important; }
        html.dark body.superadmin-shell [class~="bg-rose-50"] { background-color:#3a202b !important; }
        html.dark body.superadmin-shell [class~="bg-sky-50"] { background-color:#153144 !important; }
        html.dark body.superadmin-shell [class~="bg-violet-50"] { background-color:#2b2350 !important; }
        html.dark body.superadmin-shell [class~="text-slate-950"],
        html.dark body.superadmin-shell [class~="text-slate-900"],
        html.dark body.superadmin-shell [class~="text-slate-800"] { color:#f1f5f9 !important; }
        html.dark body.superadmin-shell [class~="text-slate-700"] { color:#d5deeb !important; }
        html.dark body.superadmin-shell [class~="text-slate-600"],
        html.dark body.superadmin-shell [class~="text-slate-500"] { color:#aab7ca !important; }
        html.dark body.superadmin-shell [class~="text-slate-400"] { color:#8291a8 !important; }
        html.dark body.superadmin-shell [class~="text-blue-800"],
        html.dark body.superadmin-shell [class~="text-blue-700"] { color:#a9c7ff !important; }
        html.dark body.superadmin-shell [class~="text-emerald-700"],
        html.dark body.superadmin-shell [class~="text-emerald-800"] { color:#8ae0b7 !important; }
        html.dark body.superadmin-shell [class~="text-amber-700"],
        html.dark body.superadmin-shell [class~="text-amber-800"],
        html.dark body.superadmin-shell [class~="text-amber-900"] { color:#f4d28a !important; }
        html.dark body.superadmin-shell [class~="text-rose-700"],
        html.dark body.superadmin-shell [class~="text-rose-800"],
        html.dark body.superadmin-shell [class~="text-rose-900"] { color:#f3aabd !important; }
        html.dark body.superadmin-shell [class~="text-sky-700"] { color:#8ed9f5 !important; }
        html.dark body.superadmin-shell nav a[aria-current="page"] > span:last-child { background-color:#7dd3fc !important; }
        html.dark body.superadmin-shell [class~="text-violet-700"] { color:#c8b7ff !important; }
        html.dark body.superadmin-shell [class~="border-slate-100"],
        html.dark body.superadmin-shell [class~="border-slate-200"],
        html.dark body.superadmin-shell [class~="border-slate-300"] { border-color:#29384e !important; }
        html.dark body.superadmin-shell input:not([type="checkbox"]),
        html.dark body.superadmin-shell select,
        html.dark body.superadmin-shell textarea { background-color:#0c1525 !important; border-color:#34445d !important; color:#f1f5f9 !important; }
        html.dark body.superadmin-shell svg circle[fill="white"] { fill:#111c2e !important; }
    </style>
    @livewireStyles
</head>
<body class="superadmin-shell min-h-screen bg-[#fafbfe] text-slate-900 antialiased">
    @php
        $secciones = [
            'Plataforma' => [
                ['superadmin.dashboard', 'Dashboard', 'squares-2x2'],
                ['superadmin.solicitudes', 'Nuevo tenant', 'plus-circle'],
                ['superadmin.tenants', 'Inventarios', 'cube'],
                ['superadmin.usuarios', 'Usuarios', 'users'],
                ['superadmin.modulos', 'Módulos', 'puzzle-piece'],
                ['superadmin.reportes', 'Reportes', 'chart-bar'],
                ['superadmin.actividad', 'Supervisión', 'arrows-right-left'],
            ],
            'Comercial' => [
                ['superadmin.planes', 'Planes', 'credit-card'],
                ['superadmin.suscripciones', 'Suscripciones', 'arrows-right-left'],
                ['superadmin.promociones', 'Promos', 'megaphone'],
            ],
            'Configuración' => [
                ['superadmin.configuracion', 'Plataforma', 'cog-6-tooth'],
            ],
        ];
        $grupoActual = collect($secciones)->first(fn ($items) => collect($items)->contains(fn ($item) => request()->routeIs($item[0])), $secciones['Plataforma']);
        $nombreGrupoActual = collect($secciones)->filter(fn ($items) => collect($items)->contains(fn ($item) => request()->routeIs($item[0])))->keys()->first() ?? 'Plataforma';
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between gap-4 px-4 md:px-10">
            <a href="{{ route('superadmin.dashboard') }}" wire:navigate class="flex items-center gap-2 font-extrabold tracking-tight text-blue-800" aria-label="MULTIVERSE, panel global">
                <span class="grid size-8 place-items-center rounded-lg bg-blue-800 text-white">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m4 8 8-4 8 4-8 4-8-4Z"/><path d="m4 12 8 4 8-4M4 16l8 4 8-4"/></svg>
                </span>
                <span class="text-xl">MULTIVERSE</span>
            </a>

            <div class="flex items-center gap-1.5">
                <button id="superadmin-theme-toggle" type="button" aria-label="Activar modo oscuro" title="Cambiar apariencia" class="grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100">
                    <svg id="superadmin-theme-moon" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.9 13A8.5 8.5 0 0 1 11 3.1 8.5 8.5 0 1 0 20.9 13Z"/></svg>
                    <svg id="superadmin-theme-sun" viewBox="0 0 24 24" class="hidden size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                </button>
                <span class="hidden max-w-[220px] truncate rounded-full px-3 py-2 text-sm font-medium text-slate-500 xl:block">MULTIVERSE · Global</span>
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-full border border-slate-200 py-1 pl-2 pr-1 transition hover:shadow-sm" aria-label="Abrir configuración y cierre de sesión">
                        <svg viewBox="0 0 24 24" class="size-3.5 text-slate-700" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span class="grid size-7 place-items-center rounded-full bg-blue-800 text-[10px] font-bold text-white">{{ auth()->user()->initials() }}</span>
                    </summary>
                    <div class="absolute right-0 top-10 z-50 w-56 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg shadow-slate-900/10">
                        <div class="px-2.5 py-2"><p class="truncate text-xs font-semibold text-slate-900">{{ auth()->user()->name }}</p><p class="text-[10px] text-slate-500">Superadministrador</p></div>
                        <div class="my-1 border-t border-slate-100"></div>
                        <a href="{{ route('superadmin.configuracion') }}" wire:navigate onclick="this.closest('details').removeAttribute('open')" @class(['flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs transition hover:bg-blue-50 hover:text-blue-800', 'bg-blue-50 font-semibold text-blue-800' => request()->routeIs('superadmin.configuracion'), 'text-slate-600' => ! request()->routeIs('superadmin.configuracion')])>
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.3a1.7 1.7 0 1 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1A1.7 1.7 0 0 0 4.2 12h-.3a1.7 1.7 0 1 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2v-.3a1.7 1.7 0 1 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.3a1.7 1.7 0 1 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg>
                            <span>Configuración</span>
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-rose-50 hover:text-rose-700">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <nav class="relative mx-auto max-w-[1440px] px-4 md:px-10" aria-label="Secciones del panel">
            <button id="superadmin-nav-left" type="button" aria-label="Secciones anteriores" class="absolute left-4 top-1/2 z-10 hidden size-8 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm md:left-10"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg></button>
            <div id="superadmin-nav" class="no-scrollbar flex justify-start gap-8 overflow-x-auto md:justify-center md:gap-10">
                @foreach ($grupoActual as [$ruta, $etiqueta, $icono])
                    @php
                        $activo = request()->routeIs($ruta);
                    @endphp
                    <a href="{{ route($ruta) }}" wire:navigate @class(['group relative flex shrink-0 flex-col items-center gap-1.5 pb-3 pt-2 text-xs font-semibold transition-colors', 'text-blue-800' => $activo, 'text-slate-500 hover:text-slate-900' => ! $activo]) @if($activo) aria-current="page" @endif>
                        <span @class(['grid size-7 place-items-center transition-transform duration-300', 'scale-110 text-blue-800' => $activo, 'text-slate-500 group-hover:-translate-y-0.5' => ! $activo])>
                            @switch($icono)
                                @case('squares-2x2')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>@break
                                @case('plus-circle')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 8v8m-4-4h8"/></svg>@break
                                @case('cube')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="M3 8v8l9 5 9-5V8m-9 5v8"/></svg>@break
                                @case('users')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 10v-2a4 4 0 0 0-3-3.87m-1-11.9a4 4 0 0 1 0 7.75"/></svg>@break
                                @case('puzzle-piece')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 13h-2a2 2 0 1 0-4 0H9v4a2 2 0 1 1-4 0v-4H3V7h5a2 2 0 1 1 4 0h4V4h4v5a2 2 0 1 0 0 4Z"/></svg>@break
                                @case('chart-bar')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10h4v10m4 0V4h4v16m4 0v-7h-4"/><path d="M2 20h20"/></svg>@break
                                @case('credit-card')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-14 5h3"/></svg>@break
                                @case('arrows-right-left')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 3l4 4-4 4M3 7h18M7 21l-4-4 4-4m14 4H3"/></svg>@break
                                @case('megaphone')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m3 11 18-5v12L3 13v-2Zm0 2v4a2 2 0 0 0 2 2h2l2-4m8-7v12"/></svg>@break
                                @case('cog-6-tooth')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.3a1.7 1.7 0 1 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1A1.7 1.7 0 0 0 4.2 12h-.3a1.7 1.7 0 1 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2v-.3a1.7 1.7 0 1 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.3a1.7 1.7 0 1 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg>@break
                            @endswitch
                        </span>
                        <span class="whitespace-nowrap">{{ $etiqueta }}</span>
                        <span @class(['absolute inset-x-0 bottom-0 h-0.5 origin-center rounded-full transition-all duration-300', 'scale-x-100 bg-blue-700' => $activo, 'scale-x-0 bg-slate-200 group-hover:scale-x-100' => ! $activo])></span>
                    </a>
                @endforeach
                @if ($nombreGrupoActual === 'Plataforma')
                    <a href="{{ route('superadmin.planes') }}" wire:navigate class="group relative flex shrink-0 flex-col items-center gap-1.5 pb-3 pt-2 text-xs font-semibold text-slate-500 transition-colors hover:text-slate-900">
                        <span class="grid size-7 place-items-center transition-transform duration-300 group-hover:-translate-y-0.5"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-14 5h3"/></svg></span>
                        <span class="whitespace-nowrap">Planes y costos</span>
                        <span class="absolute inset-x-0 bottom-0 h-0.5 origin-center scale-x-0 rounded-full bg-slate-200 transition-all duration-300 group-hover:scale-x-100"></span>
                    </a>
                @endif
            </div>
            <button id="superadmin-nav-right" type="button" aria-label="Más secciones" class="absolute right-4 top-1/2 z-10 hidden size-8 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm md:right-10"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></button>
        </nav>
    </header>

    <main class="mx-auto max-w-[1440px] px-4 py-8 md:px-10">{{ $slot }}</main>

    @livewireScripts
    @fluxScripts
    <script>
        (() => {
            const root = document.documentElement;
            const toggle = document.getElementById('superadmin-theme-toggle');
            const moon = document.getElementById('superadmin-theme-moon');
            const sun = document.getElementById('superadmin-theme-sun');
            if (!toggle || !moon || !sun) return;
            const syncThemeControl = () => {
                const dark = root.classList.contains('dark');
                moon.classList.toggle('hidden', dark);
                sun.classList.toggle('hidden', !dark);
                toggle.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
                toggle.title = dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
            };
            syncThemeControl();
            toggle.addEventListener('click', () => {
                const dark = root.classList.toggle('dark');
                const appearance = dark ? 'dark' : 'light';
                if (window.Flux && 'appearance' in window.Flux) {
                    window.Flux.appearance = appearance;
                } else {
                    localStorage.setItem('flux.appearance', appearance);
                }
                syncThemeControl();
            });
            document.addEventListener('livewire:navigated', syncThemeControl);
        })();

        (() => {
            const nav = document.getElementById('superadmin-nav');
            const left = document.getElementById('superadmin-nav-left');
            const right = document.getElementById('superadmin-nav-right');
            if (!nav || !left || !right) return;
            const update = () => {
                const overflowing = nav.scrollWidth > nav.clientWidth + 4;
                left.style.display = overflowing && nav.scrollLeft > 4 ? 'grid' : 'none';
                right.style.display = overflowing && nav.scrollLeft + nav.clientWidth < nav.scrollWidth - 4 ? 'grid' : 'none';
            };
            left.addEventListener('click', () => nav.scrollBy({ left: -280, behavior: 'smooth' }));
            right.addEventListener('click', () => nav.scrollBy({ left: 280, behavior: 'smooth' }));
            nav.addEventListener('scroll', update);
            window.addEventListener('resize', update);
            document.addEventListener('livewire:navigated', update);
            requestAnimationFrame(update);
        })();
    </script>
    @persist('page-loader')
        <x-page-loader />
    @endpersist
</body>
</html>

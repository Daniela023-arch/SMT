@props(['title' => null])

@php
    $user = auth()->user();
    $tenant = $user?->tenant;
    $tenantId = $user?->tenant_id;
    $roles = $tenantId ? $user->roles()->wherePivot('tenant_id', $tenantId)->pluck('roles.slug')->all() : [];
    $isSuperadmin = $user?->roles()->where('roles.slug', 'superadmin')->whereNull('roles.tenant_id')->wherePivot('tenant_id', null)->exists() ?? false;
    $brand = $tenant?->configuracionTenant?->color_primario;
    $brand = is_string($brand) && preg_match('/^#[0-9a-f]{6}$/i', $brand) ? $brand : '#04724d';
    $brandSecondary = $tenant?->configuracionTenant?->color_secundario;
    $brandSecondary = is_string($brandSecondary) && preg_match('/^#[0-9a-f]{6}$/i', $brandSecondary) ? $brandSecondary : '#0f172a';
    // Las rutas de operaciones requieren establecimiento y almacén; la pantalla
    // Sucursales es el selector real, no una URL inventada del prototipo React.
    $isAdmin = in_array('admin', $roles, true);
    $isVentas = in_array('ventas', $roles, true);
    $isAlmacen = in_array('almacen', $roles, true);
    $isGerente = in_array('gerente', $roles, true);
    $puntoVentaAsignado = $isVentas ? \Illuminate\Support\Facades\DB::table('usuario_almacen')
        ->join('almacenes', 'almacenes.id', '=', 'usuario_almacen.almacen_id')
        ->join('establecimientos', 'establecimientos.id', '=', 'almacenes.establecimiento_id')
        ->where('usuario_almacen.tenant_id', $tenantId)->where('usuario_almacen.usuario_id', $user->getKey())
        ->where('almacenes.tenant_id', $tenantId)->where('almacenes.activo', true)->where('establecimientos.activo', true)
        ->first(['almacenes.id', 'almacenes.establecimiento_id']) : null;
    $navItems = [
    ];
    if ($isVentas) {
        if ($puntoVentaAsignado) {
            $navItems[] = ['route' => 'ventas.index', 'parameters' => ['establecimiento' => $puntoVentaAsignado->establecimiento_id, 'almacen' => $puntoVentaAsignado->id], 'label' => 'Punto de venta', 'icon' => 'shopping-cart'];
        }
    } else {
        $navItems[] = ['route' => 'dashboard', 'label' => $isAdmin ? 'Mi tenant' : 'Inicio', 'icon' => 'layout-grid'];
    }
    if ($isAdmin || $isGerente) {
        $navItems[] = ['route' => 'tenant.usuarios', 'label' => 'Personal', 'icon' => 'users'];
    }
    if ($isAdmin || $isGerente) {
        $navItems[] = ['route' => 'productos.index', 'label' => 'Catálogo', 'icon' => 'cube'];
        $navItems[] = ['route' => 'establecimientos.index', 'label' => 'Sucursales', 'icon' => 'building-storefront'];
        $navItems[] = ['route' => 'transferencias.index', 'label' => 'Transferencias', 'icon' => 'arrows-right-left'];
    } elseif ($isAlmacen) {
        $navItems[] = ['route' => 'establecimientos.index', 'label' => 'Inventario', 'icon' => 'warehouse'];
        $navItems[] = ['route' => 'productos.index', 'label' => 'Productos', 'icon' => 'cube'];
        $navItems[] = ['route' => 'transferencias.index', 'label' => 'Traspasos', 'icon' => 'arrows-right-left'];
    }
    // El superadministrador utiliza su propio layout; nunca le aplicamos cambios aquí.

@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body class="tenant-shell multiverse-workspace min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100" style="--tenant-primary: {{ $brand }}; --tenant-secondary: {{ $brandSecondary }}; --tenant-primary-dark: color-mix(in srgb, {{ $brand }} 55%, white)">
        <header class="multiverse-workspace-header sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
            <div class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between gap-4 px-4 md:px-10">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2.5" aria-label="MULTIVERSE, inicio">
                    @if ($tenant?->configuracionTenant?->logotipo)
                        <img src="{{ str_starts_with($tenant->configuracionTenant->logotipo, 'http') ? $tenant->configuracionTenant->logotipo : \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->configuracionTenant->logotipo) }}" alt="" class="size-8 shrink-0 rounded-lg object-contain" />
                    @else
                        <x-multiverse-logo class="h-9 w-auto" />
                    @endif
                    @if ($tenant?->configuracionTenant?->logotipo)
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">MULTIVERSE</span>
                    @endif
                </a>

                <div class="flex min-w-0 items-center gap-2">
                    <button id="tenant-theme-toggle" type="button" aria-label="Activar modo oscuro" title="Cambiar tema" class="grid size-10 shrink-0 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        <svg id="tenant-theme-moon" viewBox="0 0 24 24" class="size-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.9 13A8.5 8.5 0 0 1 11 3.1 8.5 8.5 0 1 0 20.9 13Z"/></svg>
                        <svg id="tenant-theme-sun" viewBox="0 0 24 24" class="hidden size-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                    </button>
                    <span class="hidden max-w-[240px] truncate rounded-full px-3 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 md:block">
                        {{ $tenant?->nombre ?? ($isSuperadmin ? 'Global' : 'Operación') }}
                    </span>
                    <details class="relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-full border border-slate-200 py-1 pl-2 pr-1 transition hover:bg-slate-50 hover:shadow-sm dark:border-slate-700 dark:hover:bg-slate-800" aria-label="Abrir configuración y cierre de sesión">
                            <svg viewBox="0 0 24 24" class="size-4 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="grid size-8 place-items-center rounded-full text-xs font-bold text-white" style="background:linear-gradient(140deg,var(--tenant-primary),var(--tenant-secondary))">{{ $user?->initials() }}</span>
                        </summary>
                        <div class="absolute right-0 top-11 z-50 w-60 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900">
                            <div class="px-2.5 py-2"><p class="truncate text-xs font-semibold text-slate-900 dark:text-white">{{ $user?->name }}</p><p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $user?->email }}</p></div>
                            <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-800 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><span>Mi perfil</span>
                            </a>
                            <a href="{{ route('appearance.edit') }}" wire:navigate class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-800 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 1.7-3.1 2 2 0 0 1 1.7-3.1H18a3 3 0 0 0 3-3c0-4.9-4-8.8-9-8.8Z"/><path d="M7.5 10h.01m3-3h.01m4 1h.01m1 4h.01"/></svg><span>Apariencia</span>
                            </a>
                            <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-rose-50 hover:text-rose-700 dark:text-slate-300 dark:hover:bg-rose-950/40 dark:hover:text-rose-200">
                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Cerrar sesión</span>
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>

            <nav aria-label="Secciones del panel" class="mx-auto max-w-[1440px] px-4 md:px-10">
                <div class="tenant-shell-nav no-scrollbar flex justify-start gap-6 overflow-x-auto sm:gap-8 md:justify-center md:gap-10">
                    @foreach ($navItems as $item)
                        @php
                            $active = request()->routeIs($item['route']);
                            if ($item['label'] === 'Ventas') {
                                $active = request()->routeIs('ventas.index') || request()->routeIs('establecimientos.index');
                            } elseif ($item['label'] === 'Inventario') {
                                $active = request()->routeIs('inventario.show') || request()->routeIs('establecimientos.index');
                            }
                            // No resaltar Productos y Sucursales simultáneamente.
                            if ($item['route'] === 'productos.index') {
                                $active = request()->routeIs('productos.index');
                            }
                        @endphp
                        <a href="{{ route($item['route'], $item['parameters'] ?? []) }}" wire:navigate @class(['tenant-shell-nav-link group relative flex shrink-0 flex-col items-center gap-1.5 pb-3 pt-2 text-xs font-semibold transition-colors', 'tenant-shell-nav-link--active' => $active]) @if($active) aria-current="page" @endif>
                            <span class="grid size-7 place-items-center transition-transform duration-300 group-hover:-translate-y-0.5">
                                @switch($item['icon'])
                                    @case('layout-grid')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>@break
                                    @case('shopping-cart')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>@break
                                    @case('warehouse')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-7h6v7m-8-9h.01M12 12h.01m4-.01h.01"/></svg>@break
                                    @case('cube')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="M3 8v8l9 5 9-5V8m-9 5v8"/></svg>@break
                                    @case('users')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20v-1.4A5.5 5.5 0 0 1 9 13.1a5.5 5.5 0 0 1 5.5 5.5V20m.2-14.8a3.2 3.2 0 0 1 0 6.2m2.3 2a4.7 4.7 0 0 1 3.5 4.5V20"/></svg>@break
                                    @case('building-storefront')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 10h18l-2-6H5l-2 6Zm2 0v10h14V10m-9 10v-6h4v6"/><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg>@break
                                    @case('arrows-right-left')<svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M17 3l4 4-4 4M3 7h18M7 21l-4-4 4-4m14 4H3"/></svg>@break
                                @endswitch
                            </span>
                            <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                            <span class="tenant-shell-indicator absolute inset-x-0 bottom-0 h-0.5 origin-center scale-x-0 rounded-full transition-transform group-hover:scale-x-100"></span>
                        </a>
                    @endforeach
                </div>
            </nav>
        </header>

        <main class="multiverse-workspace-main mx-auto min-h-[calc(100vh-8rem)] max-w-[1440px] px-4 py-8 md:px-10">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
        <script>
            (() => {
                if (window.__multiverseThemeControlReady) return;
                window.__multiverseThemeControlReady = true;
                const root = document.documentElement;

                const sync = () => {
                    const toggle = document.getElementById('tenant-theme-toggle');
                    const moon = document.getElementById('tenant-theme-moon');
                    const sun = document.getElementById('tenant-theme-sun');
                    if (!toggle || !moon || !sun) return;
                    const dark = root.classList.contains('dark');
                    moon.classList.toggle('hidden', dark);
                    sun.classList.toggle('hidden', !dark);
                    toggle.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
                    toggle.title = dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
                };

                document.addEventListener('click', (event) => {
                    if (!(event.target instanceof Element) || !event.target.closest('#tenant-theme-toggle')) return;
                    const appearance = root.classList.contains('dark') ? 'light' : 'dark';
                    root.classList.toggle('dark', appearance === 'dark');

                    if (window.Flux && 'appearance' in window.Flux) {
                        window.Flux.appearance = appearance;
                    } else {
                        window.localStorage.setItem('flux.appearance', appearance);
                    }

                    sync();
                });

                document.addEventListener('livewire:navigated', sync);
                sync();
            })();
        </script>
        @persist('page-loader')
            <x-page-loader />
        @endpersist
    </body>
</html>

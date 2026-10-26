{{--
|--------------------------------------------------------------------------
| PLANTILLA PORTAL PRIVADO — Inventra SaaS
|--------------------------------------------------------------------------
| Uso:  <x-layouts::portal.shell :title="'Dashboard'" :activeSection="'dashboard'">
|           <x-slot:subNav>
|               <!-- módulos de la barra secundaria -->
|               <x-layouts::portal.subnav-item route="tenant.catalog" label="Catálogo" icon="package" />
|           </x-slot:subNav>
|           Contenido de la página
|       </x-layouts::portal.shell>
|
| Props:
|   $title          string   Título de la pestaña
|   $activeSection  string   Sección activa en la nav principal
|   $subNav         slot     Barra de módulos secundaria (opcional)
|   $actions        slot     Botones de acción en la cabecera (opcional)
--}}

@props([
    'title'         => null,
    'activeSection' => 'dashboard',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('appearance', 'light') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ filled($title) ? $title.' — '.config('app.name', 'Inventra') : config('app.name', 'Inventra') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any" />
    <link rel="icon" href="/favicon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" />

    {{-- Fuente: Plus Jakarta Sans (Google Fonts CDN) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>

<body class="inventra-portal min-h-screen antialiased bg-surface">

    {{-- ═══════════════════════════════════════════
         BARRA SUPERIOR — Nivel 1
         Logotipo | Nav principal | Acciones
    ═══════════════════════════════════════════ --}}
    <header class="inventra-portal-header sticky top-0 z-50 border-b border-brand-200 bg-white/98 shadow-nav backdrop-blur-sm">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6">
            <div class="flex h-14 items-center gap-4">

                {{-- LOGOTIPO / NOMBRE DEL TENANT --}}
                <a href="{{ route('dashboard') }}"
                   wire:navigate
                   class="flex shrink-0 items-center gap-2 group">
                    @if(auth()->check() && auth()->user()->tenant?->logo_url)
                        <img src="{{ auth()->user()->tenant->logo_url }}"
                             alt="{{ auth()->user()->tenant->name }}"
                             class="h-8 w-auto object-contain rounded" />
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-700 group-hover:bg-brand-600 transition-colors"
                             style="{{ auth()->user()?->tenant?->primary_color ? 'background-color:'.auth()->user()->tenant->primary_color : '' }}">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-brand-800 max-w-[140px] truncate">
                            {{ auth()->user()?->tenant?->name ?? config('app.name', 'Inventra') }}
                        </span>
                    @endif
                </a>

                {{-- SEPARADOR VERTICAL --}}
                <div class="hidden h-6 w-px bg-slate-200 lg:block" aria-hidden="true"></div>

                {{-- NAVEGACIÓN PRINCIPAL (desktop) --}}
                @php
                    $role = auth()->user()?->role ?? 'encargado';

                    $mainNav = match(true) {
                        $role === 'superadmin' => [
                            ['id' => 'dashboard',  'label' => 'Dashboard',     'route' => 'superadmin.dashboard',  'icon' => 'layout-grid'],
                            ['id' => 'tenants',    'label' => 'Nuevo tenant',  'route' => 'superadmin.tenants',    'icon' => 'building-2'],
                            ['id' => 'inventories','label' => 'Inventarios',   'route' => 'superadmin.inventories','icon' => 'package'],
                            ['id' => 'users',      'label' => 'Usuarios',      'route' => 'superadmin.users',      'icon' => 'users'],
                            ['id' => 'modules',    'label' => 'Módulos',       'route' => 'superadmin.modules',    'icon' => 'puzzle'],
                            ['id' => 'reports',    'label' => 'Reportes',      'route' => 'superadmin.reports',    'icon' => 'bar-chart-2'],
                        ],
                        $role === 'admin' => [
                            ['id' => 'dashboard', 'label' => 'Mi tenant',  'route' => 'admin.dashboard', 'icon' => 'layout-grid'],
                            ['id' => 'users',     'label' => 'Usuarios',   'route' => 'admin.users',     'icon' => 'users'],
                            ['id' => 'catalog',   'label' => 'Catálogo',   'route' => 'admin.catalog',   'icon' => 'package'],
                            ['id' => 'reports',   'label' => 'Reportes',   'route' => 'admin.reports',   'icon' => 'bar-chart-2'],
                        ],
                        $role === 'encargado_ventas' => [
                            ['id' => 'dashboard', 'label' => 'Inicio',    'route' => 'ventas.dashboard', 'icon' => 'home'],
                            ['id' => 'ventas',    'label' => 'Ventas',    'route' => 'ventas.index',     'icon' => 'shopping-cart'],
                            ['id' => 'productos', 'label' => 'Productos', 'route' => 'ventas.products',  'icon' => 'tag'],
                            ['id' => 'historial', 'label' => 'Historial', 'route' => 'ventas.history',   'icon' => 'clock'],
                        ],
                        $role === 'encargado_prestamos' => [
                            ['id' => 'dashboard', 'label' => 'Inicio',       'route' => 'prestamos.dashboard', 'icon' => 'home'],
                            ['id' => 'prestamos', 'label' => 'Préstamos',    'route' => 'prestamos.index',     'icon' => 'book-open'],
                            ['id' => 'devolu',    'label' => 'Devoluciones', 'route' => 'prestamos.returns',   'icon' => 'corner-down-left'],
                            ['id' => 'historial', 'label' => 'Historial',    'route' => 'prestamos.history',   'icon' => 'clock'],
                        ],
                        $role === 'encargado_almacen' => [
                            ['id' => 'dashboard',   'label' => 'Inicio',      'route' => 'almacen.dashboard',   'icon' => 'home'],
                            ['id' => 'inventario',  'label' => 'Inventario',  'route' => 'almacen.inventory',   'icon' => 'package'],
                            ['id' => 'entradas',    'label' => 'Entradas',    'route' => 'almacen.entries',     'icon' => 'log-in'],
                            ['id' => 'salidas',     'label' => 'Salidas',     'route' => 'almacen.exits',       'icon' => 'log-out'],
                            ['id' => 'movimientos', 'label' => 'Movimientos', 'route' => 'almacen.movements',   'icon' => 'arrow-left-right'],
                        ],
                        default => [],
                    };
                @endphp

                <nav class="hidden lg:flex flex-1 items-center gap-0.5 overflow-x-auto no-scrollbar" aria-label="Navegación del portal">
                    @foreach($mainNav as $item)
                        <a href="{{ route($item['route']) }}"
                           wire:navigate
                           class="inventra-portal-nav-link {{ $activeSection === $item['id'] ? 'inventra-portal-nav-link--active' : '' }}"
                           @if($activeSection === $item['id']) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- ACCIONES DEL SLOT (derecha) --}}
                @isset($actions)
                    <div class="hidden lg:flex items-center gap-2">
                        {{ $actions }}
                    </div>
                @endisset

                {{-- ZONA USUARIO + UTIL --}}
                <div class="ml-auto flex shrink-0 items-center gap-2">

                    {{-- Búsqueda rápida --}}
                    <button
                        type="button"
                        class="inventra-icon-btn hidden sm:flex"
                        aria-label="Buscar"
                        title="Buscar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
                        </svg>
                    </button>

                    {{-- Notificaciones --}}
                    <button
                        type="button"
                        class="inventra-icon-btn relative"
                        aria-label="Notificaciones"
                        title="Notificaciones">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        {{-- Badge de notificaciones --}}
                        @livewire('notifications-badge')
                    </button>

                    {{-- MENÚ USUARIO --}}
                    <div x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open"
                            @click.away="open = false"
                            type="button"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm
                                   text-slate-700 hover:bg-slate-50 transition-colors"
                            aria-haspopup="true"
                            :aria-expanded="open">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-700 text-white text-xs font-semibold select-none"
                                 style="{{ auth()->user()?->tenant?->primary_color ? 'background-color:'.auth()->user()->tenant->primary_color : '' }}">
                                {{ auth()->user()?->initials() ?? 'U' }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-semibold text-slate-800 leading-none truncate max-w-[120px]">
                                    {{ auth()->user()?->name ?? 'Usuario' }}
                                </p>
                                <p class="text-[10px] text-slate-500 leading-none truncate max-w-[120px] mt-0.5">
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </p>
                            </div>
                            <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
                                 :class="open && 'rotate-180'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                            class="absolute right-0 mt-1 w-52 rounded-xl border border-slate-200 bg-white p-1 shadow-lift z-50"
                            x-cloak>

                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()?->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                            </div>

                            <a href="{{ route('profile.edit') }}" wire:navigate
                               class="inventra-dropdown-item">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Mi perfil
                            </a>

                            @if($role === 'admin')
                                <a href="{{ route('admin.settings') }}" wire:navigate
                                   class="inventra-dropdown-item">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Configuración
                                </a>
                            @endif

                            <div class="my-1 border-t border-slate-100"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="inventra-dropdown-item w-full text-red-600 hover:bg-red-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Botón hamburguesa mobile --}}
                    <button
                        type="button"
                        class="inventra-icon-btn lg:hidden"
                        aria-label="Menú"
                        x-data
                        @click="$dispatch('toggle-portal-menu')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════
             BARRA SECUNDARIA — Módulos (Nivel 2, estilo Airbnb)
             Solo se renderiza si hay items en $subNav
        ═══════════════════════════════ --}}
        @isset($subNav)
            <div class="border-t border-slate-100 bg-white">
                <div class="mx-auto max-w-screen-2xl px-4 sm:px-6">
                    <div class="relative flex items-center">
                        {{-- Flecha izquierda --}}
                        <button type="button"
                                id="subnav-scroll-left"
                                class="inventra-subnav-arrow left-0"
                                aria-label="Desplazar módulos a la izquierda">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div id="subnav-scroll-container"
                             class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth py-0.5">
                            {{ $subNav }}
                        </div>

                        {{-- Flecha derecha --}}
                        <button type="button"
                                id="subnav-scroll-right"
                                class="inventra-subnav-arrow right-0"
                                aria-label="Desplazar módulos a la derecha">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endisset
    </header>

    {{-- ═══════════════════════════════════════════
         MENÚ MOBILE (drawer)
    ═══════════════════════════════════════════ --}}
    <div
        x-data="{ open: false }"
        @toggle-portal-menu.window="open = !open"
        x-show="open"
        x-cloak
        class="lg:hidden fixed inset-0 z-40 flex"
        role="dialog"
        aria-modal="true">

        {{-- Overlay --}}
        <div @click="open = false"
             class="fixed inset-0 bg-black/40 backdrop-blur-sm"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-end="opacity-0">
        </div>

        {{-- Panel --}}
        <div x-transition:enter="transition-transform duration-250 ease-out"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition-transform duration-200 ease-in"
             x-transition:leave-end="-translate-x-full"
             class="relative z-50 flex w-64 flex-col bg-white shadow-2xl">

            <div class="flex h-14 items-center justify-between border-b border-slate-100 px-4">
                <span class="text-sm font-semibold text-brand-800">
                    {{ auth()->user()?->tenant?->name ?? config('app.name', 'Inventra') }}
                </span>
                <button @click="open = false" class="inventra-icon-btn" aria-label="Cerrar menú">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-3 px-2">
                @foreach($mainNav ?? [] as $item)
                    <a href="{{ route($item['route']) }}"
                       wire:navigate
                       @click="open = false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                              {{ $activeSection === $item['id']
                                 ? 'bg-brand-50 text-brand-700'
                                 : 'text-slate-600 hover:bg-slate-50 hover:text-brand-700' }}
                              transition-colors">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-100 p-3">
                <div class="flex items-center gap-2 px-2 py-1.5 mb-1">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-700 text-white text-xs font-semibold">
                        {{ auth()->user()?->initials() ?? 'U' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-800 truncate">{{ auth()->user()?->name }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ═══════════════════════════════════════════ --}}
    <main id="main-content" class="inventra-portal-main mx-auto max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    {{-- ═══════════════════════════════════════════
         NOTIFICACIONES TOAST
    ═══════════════════════════════════════════ --}}
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    {{-- Script: flechas de la barra secundaria --}}
    <script>
        (function () {
            const container = document.getElementById('subnav-scroll-container');
            const left      = document.getElementById('subnav-scroll-left');
            const right     = document.getElementById('subnav-scroll-right');
            if (!container) return;
            left?.addEventListener('click',  () => container.scrollBy({ left: -160, behavior: 'smooth' }));
            right?.addEventListener('click', () => container.scrollBy({ left:  160, behavior: 'smooth' }));
        })();
    </script>

    @livewireScripts
    @fluxScripts
</body>
</html>

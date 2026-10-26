{{--
|--------------------------------------------------------------------------
| PLANTILLA PÚBLICA — Inventra SaaS
|--------------------------------------------------------------------------
| Uso:  <x-layouts::public.base :title="'Inicio'" :activeNav="'inicio'">
|           <x-slot:hero> ... </x-slot:hero>   ← opcional
|           Contenido principal
|       </x-layouts::public.base>
|
| Props:
|   $title      string   Título de la pestaña
|   $activeNav  string   Enlace activo: inicio|funcionalidades|tipos|planes|solicitar
|   $hero       slot     Sección hero opcional (se inyecta antes del $slot)
--}}

@props([
    'title'     => null,
    'activeNav' => 'inicio',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ $metaDescription ?? 'Inventra — Plataforma SaaS inteligente para administrar inventarios de múltiples negocios.' }}" />

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
</head>

<body class="inventra-public min-h-screen antialiased">

    {{-- ═══════════════════════════════════════════
         NAVBAR PRINCIPAL (nivel 1)
    ═══════════════════════════════════════════ --}}
    <header class="inventra-nav sticky top-0 z-50 border-b border-brand-200 bg-white/95 backdrop-blur-sm shadow-nav">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">

                {{-- LOGOTIPO --}}
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 group" wire:navigate>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-700 group-hover:bg-brand-600 transition-colors">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-brand-800">Inventra</span>
                </a>

                {{-- NAVEGACIÓN CENTRADA (desktop) --}}
                <nav class="hidden lg:flex items-center gap-1" aria-label="Navegación principal">
                    @php
                        $navLinks = [
                            ['id' => 'inicio',          'label' => 'Inicio',              'route' => 'home'],
                            ['id' => 'funcionalidades',  'label' => 'Funcionalidades',     'route' => 'features'],
                            ['id' => 'tipos',            'label' => 'Tipos de inventario', 'route' => 'inventory-types'],
                            ['id' => 'planes',           'label' => 'Planes',              'route' => 'plans'],
                            ['id' => 'solicitar',        'label' => 'Solicitar sistema',   'route' => 'request-tenant'],
                        ];
                    @endphp

                    @foreach($navLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           wire:navigate
                           class="inventra-nav-link {{ $activeNav === $link['id'] ? 'inventra-nav-link--active' : '' }}"
                           @if($activeNav === $link['id']) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- ACCIONES DERECHA --}}
                <div class="flex shrink-0 items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}"
                           wire:navigate
                           class="inventra-btn inventra-btn--ghost hidden sm:inline-flex">
                            Iniciar sesión
                        </a>
                        <a href="{{ route('request-tenant') }}"
                           wire:navigate
                           class="inventra-btn inventra-btn--primary">
                            Solicitar sistema
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                           wire:navigate
                           class="inventra-btn inventra-btn--primary">
                            Ir al panel
                        </a>
                    @endguest

                    {{-- Botón hamburguesa mobile --}}
                    <button
                        type="button"
                        class="inventra-mobile-toggle lg:hidden"
                        aria-label="Abrir menú"
                        x-data
                        @click="$dispatch('toggle-mobile-menu')"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- MENÚ MOBILE --}}
        <div
            x-data="{ open: false }"
            @toggle-mobile-menu.window="open = !open"
            x-show="open"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="lg:hidden border-t border-brand-100 bg-white px-4 pb-4 pt-2 shadow-md"
            x-cloak
        >
            @foreach($navLinks ?? [] as $link)
                <a href="{{ route($link['route']) }}"
                   wire:navigate
                   class="block rounded-lg px-3 py-2 text-sm font-medium
                          {{ $activeNav === $link['id']
                             ? 'bg-brand-50 text-brand-700'
                             : 'text-slate-600 hover:bg-slate-50 hover:text-brand-700' }}
                          transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach
            @guest
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <a href="{{ route('login') }}" wire:navigate
                       class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Iniciar sesión
                    </a>
                </div>
            @endguest
        </div>
    </header>

    {{-- ═══════════════════════════════════════════
         SLOT HERO (opcional)
    ═══════════════════════════════════════════ --}}
    @isset($hero)
        <section class="inventra-hero">
            {{ $hero }}
        </section>
    @endisset

    {{-- ═══════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ═══════════════════════════════════════════ --}}
    <main id="main-content" class="inventra-main">
        {{ $slot }}
    </main>

    {{-- ═══════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════ --}}
    <footer class="inventra-footer border-t border-brand-100 bg-brand-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">

                {{-- Columna marca --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold">Inventra</span>
                    </div>
                    <p class="text-sm text-brand-300 max-w-xs leading-relaxed">
                        Plataforma inteligente y adaptable para administrar productos, existencias,
                        ventas, préstamos y operaciones desde un solo lugar.
                    </p>
                </div>

                {{-- Columna producto --}}
                <div>
                    <h3 class="text-sm font-semibold text-brand-200 uppercase tracking-wider mb-3">Producto</h3>
                    <ul class="space-y-2 text-sm text-brand-300">
                        <li><a href="{{ route('features') }}" wire:navigate class="hover:text-white transition-colors">Funcionalidades</a></li>
                        <li><a href="{{ route('inventory-types') }}" wire:navigate class="hover:text-white transition-colors">Tipos de inventario</a></li>
                        <li><a href="{{ route('plans') }}" wire:navigate class="hover:text-white transition-colors">Planes y precios</a></li>
                        <li><a href="{{ route('request-tenant') }}" wire:navigate class="hover:text-white transition-colors">Solicitar sistema</a></li>
                    </ul>
                </div>

                {{-- Columna soporte --}}
                <div>
                    <h3 class="text-sm font-semibold text-brand-200 uppercase tracking-wider mb-3">Soporte</h3>
                    <ul class="space-y-2 text-sm text-brand-300">
                        <li><a href="#" class="hover:text-white transition-colors">Centro de ayuda</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contacto</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Aviso de privacidad</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Términos de servicio</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t border-brand-700 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-brand-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Inventra') }}. Todos los derechos reservados.
                </p>
                <p class="text-xs text-brand-500">Versión 1.0</p>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════
         NOTIFICACIONES TOAST
    ═══════════════════════════════════════════ --}}
    @livewire('notifications')

    @livewireScripts
</body>
</html>

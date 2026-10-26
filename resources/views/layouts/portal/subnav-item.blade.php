{{--
| Componente: subnav-item
| Uso dentro de <x-slot:subNav> en la plantilla portal/shell
|
| Ejemplo:
|   <x-layouts::portal.subnav-item
|       route="admin.catalog"
|       label="Catálogo"
|       icon="package"
|       :active="request()->routeIs('admin.catalog*')" />
--}}

@props([
    'route'  => '#',
    'label'  => '',
    'icon'   => null,
    'active' => false,
])

<a href="{{ $route === '#' ? '#' : route($route) }}"
   @if($route !== '#') wire:navigate @endif
   class="inventra-subnav-item {{ $active ? 'inventra-subnav-item--active' : '' }}"
   @if($active) aria-current="page" @endif>

    @if($icon)
        {{-- Iconos lineales Heroicons / Lucide via SVG sprite o inline --}}
        @switch($icon)
            @case('package')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                @break
            @case('tag')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 7h.01M7 3H5a2 2 0 00-2 2v2a2 2 0 00.586 1.414l7 7a2 2 0 002.828 0l4-4a2 2 0 000-2.828l-7-7A2 2 0 007 3z"/>
                </svg>
                @break
            @case('map-pin')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                @break
            @case('truck')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M1 1h4l2.68 13.39a2 2 0 001.98 1.61h9.72a2 2 0 001.98-1.61L23 6H6"/>
                </svg>
                @break
            @case('book-open')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
                @break
            @case('shopping-cart')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7A1 1 0 006.5 17h11a1 1 0 00.9-1.45L17 13M10 20a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z"/>
                </svg>
                @break
            @case('bar-chart-2')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 20V10M12 20V4M6 20v-6"/>
                </svg>
                @break
            @case('clock')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.75"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                </svg>
                @break
            @case('corner-down-left')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 10l-5 5 5 5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 4v7a4 4 0 01-4 4H4"/>
                </svg>
                @break
            @case('log-in')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="1.75"/>
                </svg>
                @break
            @case('log-out')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="1.75"/>
                </svg>
                @break
            @case('arrow-left-right')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7H3M3 7l4-4M3 7l4 4M3 17h18M21 17l-4-4M21 17l-4 4"/>
                </svg>
                @break
            @case('users')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.75"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                @break
            @default
                {{-- Icono genérico si no se reconoce --}}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.75"/>
                </svg>
        @endswitch
    @endif

    <span>{{ $label }}</span>
</a>

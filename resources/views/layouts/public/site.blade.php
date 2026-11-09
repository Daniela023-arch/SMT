<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MULTIVERSE ayuda a ferreterías a controlar productos, existencias, ventas y transferencias entre sucursales.">
    <title>{{ $title ?? 'MULTIVERSE | Inventario para ferreterías' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 md:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold tracking-tight text-slate-900" wire:navigate>
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-700 text-white">M</span><span class="text-xl">MULTIVERSE</span>
            </a>
            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navegación principal">
                <a class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" href="{{ route('home') }}#inicio">Inicio</a>
                <a class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" href="{{ route('home') }}#funcionalidades">Funcionalidades</a>
                <a class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" href="{{ route('home') }}#ferreteria">Solución ferretera</a>
                <a class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" href="{{ route('home') }}#planes">Planes</a>
                <a class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" href="{{ route('public.solicitar') }}" wire:navigate>Solicitar sistema</a>
            </nav>
            <div class="flex items-center gap-2">
                <a class="inline-flex rounded-full px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('login') }}" wire:navigate>Iniciar sesión</a>
                <a class="rounded-full bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-800 lg:hidden" href="{{ route('public.solicitar') }}" wire:navigate>Solicitar</a>
            </div>
        </div>
    </header>
    {{ $slot }}
    <footer class="bg-slate-950 text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 md:grid-cols-3 md:px-8">
            <div><div class="text-xl font-extrabold">MULTIVERSE</div><p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">Control de inventario, ventas y operación para ferreterías con una o varias sucursales.</p></div>
            <div><h2 class="font-semibold">Explora</h2><div class="mt-3 grid gap-2 text-sm text-slate-400"><a href="{{ route('home') }}#funcionalidades">Funcionalidades</a><a href="{{ route('home') }}#planes">Planes</a><a href="{{ route('public.solicitar') }}" wire:navigate>Solicitar información</a></div></div>
            <p class="text-sm text-slate-400">Tu solicitud se revisa antes de activar un espacio de trabajo. La contratación y configuración se confirman contigo.</p>
        </div>
        <div class="border-t border-white/10 px-4 py-5 text-center text-xs text-slate-500">© {{ date('Y') }} MULTIVERSE. Todos los derechos reservados.</div>
    </footer>
    @livewireScripts
</body>
</html>

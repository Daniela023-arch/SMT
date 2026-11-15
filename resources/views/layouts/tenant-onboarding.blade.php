<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @fluxAppearance
</head>
<body class="min-h-screen bg-slate-50 antialiased dark:bg-slate-950">
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('onboarding.show') }}" class="flex items-center gap-2.5 font-bold text-slate-900 dark:text-white">
                <span class="grid size-9 place-items-center rounded-xl bg-blue-700 text-white" aria-hidden="true">M</span>
                <span>MULTIVERSE <span class="font-normal text-slate-500 dark:text-slate-400">· {{ auth()->user()?->tenant?->nombre }}</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Cerrar sesión</button>
            </form>
        </div>
    </header>
    <main>{{ $slot }}</main>
    @fluxScripts
    <x-page-loader />
</body>
</html>

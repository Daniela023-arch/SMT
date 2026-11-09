@props(['title' => 'Iniciar sesión'])

<!DOCTYPE html>
<html lang="es">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100 antialiased">
        <main class="relative flex min-h-screen overflow-hidden">
            <img src="{{ asset('images/login-tools.png') }}" alt="Herramientas de ferretería sobre una mesa de trabajo" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-slate-900/10"></div>

            <svg class="pointer-events-none absolute inset-0 z-10 hidden h-full w-full lg:block" viewBox="0 0 1000 800" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 0H390C425 88 454 169 492 278C524 369 578 440 574 501C568 608 478 675 444 800H0Z" fill="#fff" />
            </svg>

            <section class="relative z-20 hidden w-[58%] flex-col justify-between px-12 py-10 lg:flex xl:px-20">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex w-fit items-center gap-3" aria-label="MULTIVERSE, volver al inicio">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-700 text-white shadow-sm">
                        <svg viewBox="0 0 32 32" class="h-7 w-7" fill="none" aria-hidden="true"><path d="m16 3 11 6.2v13.6L16 29 5 22.8V9.2L16 3Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="m5.8 9.7 10.2 5.8 10.2-5.8M16 15.5V28" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="text-2xl font-extrabold tracking-tight text-slate-950">MULTIVERSE</span>
                </a>

                <div class="max-w-[34rem] pb-12 pl-1 xl:pb-16">
                    <h2 class="text-4xl font-extrabold leading-[1.12] tracking-tight text-slate-950 xl:text-5xl">Gestión integral para ferreterías multisucursal</h2>
                    <p class="mt-6 max-w-lg text-lg leading-8 text-slate-600">Inventarios, ventas, compras y transferencias en una sola plataforma.</p>
                </div>

                <p class="text-xs font-medium text-slate-500">© {{ date('Y') }} MULTIVERSE · Plataforma para ferreterías</p>
            </section>

            <section class="relative z-20 ml-auto flex min-h-screen w-full items-center justify-center bg-white/95 px-5 py-8 backdrop-blur-sm sm:px-10 lg:w-[42%] lg:bg-white/90 xl:px-14">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" wire:navigate class="mb-8 inline-flex items-center gap-2 font-extrabold tracking-tight text-slate-950 lg:hidden">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-700 text-white">M</span>MULTIVERSE
                    </a>
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_20px_70px_-24px_rgba(15,23,42,.24)] sm:p-9">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-sm text-slate-600">¿Aún no tienes una cuenta?
                        <a href="{{ route('public.solicitar') }}" wire:navigate class="ml-1 font-bold text-indigo-700 underline decoration-indigo-300 underline-offset-4 hover:text-indigo-900">Solicitar el sistema</a>
                    </p>
                </div>
            </section>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

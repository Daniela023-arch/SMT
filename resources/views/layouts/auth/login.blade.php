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
                    <x-multiverse-logo class="h-10 w-auto" />
                </a>

                <div class="max-w-[34rem] pb-12 pl-1 xl:pb-16">
                    <h2 class="text-4xl font-extrabold leading-[1.12] tracking-tight text-slate-950 xl:text-5xl">Gestión integral para ferreterías multisucursal</h2>
                    <p class="mt-6 max-w-lg text-lg leading-8 text-slate-600">Inventarios, ventas, compras y transferencias en una sola plataforma.</p>
                </div>

                <p class="text-xs font-medium text-slate-500">© {{ date('Y') }} MULTIVERSE · Plataforma para ferreterías</p>
            </section>

            <section class="relative z-20 ml-auto flex min-h-screen w-full items-center justify-center bg-white/95 px-5 py-8 backdrop-blur-sm sm:px-10 lg:w-[42%] lg:bg-white/90 xl:px-14">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" wire:navigate class="mb-8 inline-flex items-center" aria-label="MULTIVERSE, volver al inicio">
                        <x-multiverse-logo class="h-9 w-auto" />
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
        <x-page-loader />
    </body>
</html>

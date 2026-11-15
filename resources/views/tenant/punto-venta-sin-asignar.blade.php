<x-layouts::app title="Punto de venta sin asignar | MULTIVERSE">
    <main class="mx-auto grid min-h-[55vh] max-w-2xl place-items-center">
        <section class="w-full rounded-2xl border border-amber-200 bg-white p-6 shadow-sm dark:border-amber-900 dark:bg-slate-900 sm:p-8">
            <span class="grid size-12 place-items-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 2.8 20h18.4L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg>
            </span>
            <p class="mt-5 text-sm font-semibold text-amber-800 dark:text-amber-300">Falta una asignación</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Tu punto de venta aún no está configurado</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">El administrador de {{ $tenant->nombre }} debe asignarte una caja antes de que puedas registrar ventas.</p>

            <ol class="mt-5 space-y-2 rounded-xl bg-slate-50 p-4 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <li><span class="font-semibold">1.</span> Cierra sesión e ingresa con la cuenta administradora.</li>
                <li><span class="font-semibold">2.</span> Abre <span class="font-semibold">Personal y accesos</span> y edita tu usuario.</li>
                <li><span class="font-semibold">3.</span> Elige un <span class="font-semibold">Punto de venta asignado</span> y guarda los cambios.</li>
            </ol>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-800 sm:w-auto">Cerrar sesión</button>
            </form>
        </section>
    </main>
</x-layouts::app>

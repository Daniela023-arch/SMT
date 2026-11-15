<div class="min-h-screen bg-slate-50 px-4 py-10 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6">
    <div class="mx-auto max-w-4xl">
        <header class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Primer acceso</p>
            <h1 class="mt-2 text-3xl font-bold">Personaliza {{ $tenant->nombre }}</h1>
            <p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-300">Configura la identidad visual, importa tu catálogo si ya lo tienes y termina con el pago del plan para activar el espacio.</p>
        </header>

        <div class="mb-6 grid gap-3 sm:grid-cols-3" aria-label="Pasos de configuración">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-900 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-100">1. Personalización</div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">2. Catálogo opcional</div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">3. Pago y activación</div>
        </div>

        @if ($suscripcion)
            <section class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div>
                    <h2 class="text-lg font-semibold">Identidad de tu empresa</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Los colores y el logotipo aparecerán en el espacio de trabajo.</p>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="block text-sm font-medium">Color principal
                            <div class="mt-2 flex items-center gap-3 rounded-lg border border-slate-200 p-2 dark:border-slate-700">
                                <input type="color" wire:model="colorPrimario" class="size-10 cursor-pointer rounded border-0 bg-transparent p-0">
                                <input type="text" wire:model="colorPrimario" maxlength="7" class="w-full border-0 bg-transparent font-mono text-sm focus:ring-0">
                            </div>
                            @error('colorPrimario') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-medium">Color secundario
                            <div class="mt-2 flex items-center gap-3 rounded-lg border border-slate-200 p-2 dark:border-slate-700">
                                <input type="color" wire:model="colorSecundario" class="size-10 cursor-pointer rounded border-0 bg-transparent p-0">
                                <input type="text" wire:model="colorSecundario" maxlength="7" class="w-full border-0 bg-transparent font-mono text-sm focus:ring-0">
                            </div>
                            @error('colorSecundario') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <label class="block text-sm font-semibold">Logotipo <span class="font-normal text-slate-500">(opcional, PNG/JPG/WebP)</span></label>
                    <input type="file" wire:model="logo" accept="image/*" class="mt-3 block w-full rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                    @error('logo') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="logo" class="mt-2 text-xs text-blue-700">Subiendo logotipo…</div>
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="Vista previa del logotipo" class="mt-3 h-16 max-w-48 rounded border border-slate-200 bg-white object-contain p-2">
                    @elseif ($logoActual)
                        <img src="{{ str_starts_with($logoActual, 'http') ? $logoActual : Storage::disk('public')->url($logoActual) }}" alt="Logotipo actual" class="mt-3 h-16 max-w-48 rounded border border-slate-200 bg-white object-contain p-2">
                    @endif
                </div>

                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Carga masiva de productos <span class="text-sm font-normal text-slate-500">(opcional)</span></h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sube un CSV de hasta 5,000 filas. SKU existente se actualiza en este tenant; filas inválidas se reportan sin detener las demás.</p>
                    <p class="mt-2 text-xs text-slate-500">Encabezados: <code class="rounded bg-slate-100 px-1 py-0.5 dark:bg-slate-800">sku,nombre,unidad_medida,precio_venta,stock_minimo,descripcion</code></p>
                    <input type="file" wire:model="archivoCsv" accept=".csv,text/csv" class="mt-3 block w-full rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                    @error('archivoCsv') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    @if ($resultadoImportacion)
                        <div class="mt-3 rounded-lg bg-blue-50 p-4 text-sm text-blue-950 dark:bg-blue-950 dark:text-blue-100">
                            Importación: {{ $resultadoImportacion['exitosas'] }} de {{ $resultadoImportacion['total'] }} filas procesadas correctamente; {{ $resultadoImportacion['fallidas'] }} con error.
                            @if ($resultadoImportacion['errores'])
                                <ul class="mt-2 list-inside list-disc text-xs">@foreach ($resultadoImportacion['errores'] as $error)<li>Fila {{ $error['fila'] }}: {{ $error['mensaje'] }}</li>@endforeach</ul>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Activar el plan {{ $suscripcion->plan->nombre }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">${{ number_format((float) $suscripcion->plan->precio, 2) }} {{ $suscripcion->plan->moneda }} / {{ $suscripcion->plan->periodicidad }}</p>
                            <p class="mt-2 text-xs text-slate-500">El acceso se habilita únicamente cuando Mercado Pago confirme el primer pago.</p>
                        </div>
                        @if ($suscripcion->estado === 'activa' && $tenant->estado === 'activo')
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">Ir al panel</a>
                        @else
                            <button type="button" wire:click="iniciarPago" wire:loading.attr="disabled" class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                                <span wire:loading.remove wire:target="iniciarPago">Guardar y continuar al pago</span>
                                <span wire:loading wire:target="iniciarPago">Preparando pago…</span>
                            </button>
                        @endif
                    </div>
                    @if (app()->isLocal() && ! ($suscripcion->estado === 'activa' && $tenant->estado === 'activo'))
                        <a href="{{ route('onboarding.simulacion') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-800 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200 dark:hover:bg-blue-900">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            Vista previa del checkout (simulación local)
                        </a>
                    @endif
                    @if ($errorPago)<p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950 dark:text-red-200">{{ $errorPago }}</p>@endif
                    @if ($suscripcion->suscripcion_externa_id && ! ($suscripcion->estado === 'activa' && $tenant->estado === 'activo'))
                        <div class="mt-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-100">
                            El pago está en proceso. La activación se confirmará automáticamente al recibir el evento de pago aprobado.
                            <button type="button" wire:click="revisarPago" class="ml-2 font-semibold underline">Revisar estado</button>
                            @if ($estadoPago)<span class="ml-2 text-xs">Estado reportado: {{ $estadoPago }}</span>@endif
                        </div>
                    @endif
                </div>
            </section>
        @else
            <div class="rounded-xl bg-amber-50 p-5 text-amber-900 dark:bg-amber-950 dark:text-amber-100">No hay un plan asignado a este espacio. Contacta al superadministrador.</div>
        @endif
    </div>
</div>

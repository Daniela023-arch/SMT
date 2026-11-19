<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vista previa de pago | MULTIVERSE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#eef2f8] text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('onboarding.show') }}" class="flex items-center" aria-label="Volver a MULTIVERSE">
                <x-multiverse-logo class="h-9 w-auto" />
            </a>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">Simulación local · sin cobro</span>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('onboarding.show') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900"><span aria-hidden="true">←</span> Regresar a la personalización</a>
            <div class="mt-6 grid overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5 md:grid-cols-[1.1fr_.9fr]">
                <section class="p-6 sm:p-9">
                    <div class="flex items-center gap-3">
                        @if ($tenant->configuracionTenant?->logotipo)
                            <img src="{{ str_starts_with($tenant->configuracionTenant->logotipo, 'http') ? $tenant->configuracionTenant->logotipo : \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->configuracionTenant->logotipo) }}" alt="Logotipo de {{ $tenant->nombre }}" class="size-12 rounded-xl border border-slate-200 object-contain p-1">
                        @else
                            <span class="grid size-12 place-items-center rounded-xl bg-blue-50 text-lg font-bold text-blue-800">{{ mb_strtoupper(mb_substr($tenant->nombre, 0, 1)) }}</span>
                        @endif
                        <div><p class="text-xs font-medium text-slate-500">Suscripción para</p><p class="font-semibold text-slate-900">{{ $tenant->nombre }}</p></div>
                    </div>

                    <h1 class="mt-9 text-2xl font-bold tracking-tight sm:text-3xl">Confirma tu plan</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Así podría verse el paso de pago recurrente antes de continuar con Mercado Pago.</p>

                    <div class="mt-7 rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div><p class="font-semibold text-slate-900">Plan {{ $plan->nombre }}</p><p class="mt-1 text-sm text-slate-500">Suscripción {{ mb_strtolower($plan->periodicidad) }}</p></div>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">MULTIVERSE</span>
                        </div>
                        <div class="my-4 border-t border-dashed border-slate-200"></div>
                        <div class="flex items-end justify-between gap-4"><span class="text-sm text-slate-600">Total recurrente</span><span class="text-2xl font-bold text-slate-950">${{ number_format((float) $plan->precio, 2) }} <span class="text-sm font-medium text-slate-500">{{ $plan->moneda }}</span></span></div>
                        <p class="mt-1 text-right text-xs text-slate-500">Se cobra {{ mb_strtolower($plan->periodicidad) }}</p>
                    </div>

                    <div class="mt-7">
                        <h2 class="text-sm font-semibold text-slate-900">Método de pago</h2>
                        <div class="mt-3 flex items-center gap-3 rounded-xl border-2 border-blue-600 bg-blue-50/50 p-4">
                            <span class="grid size-5 place-items-center rounded-full border-2 border-blue-700"><span class="size-2.5 rounded-full bg-blue-700"></span></span>
                            <span class="grid size-10 place-items-center rounded-lg bg-white text-blue-700 shadow-sm"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-14 5h3"/></svg></span>
                            <span><span class="block text-sm font-semibold text-slate-900">Tarjeta de crédito o débito</span><span class="mt-0.5 block text-xs text-slate-500">Se solicitará en el checkout seguro del proveedor.</span></span>
                        </div>
                    </div>

                    <button type="button" disabled class="mt-7 w-full cursor-not-allowed rounded-xl bg-blue-700 px-5 py-3.5 text-sm font-bold text-white opacity-60">Continuar al pago · desactivado en la simulación</button>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">Esta vista previa no envía información, no crea un cobro y no activa el tenant.</p>
                </section>

                <aside class="border-t border-slate-200 bg-[#f8fafc] p-6 md:border-l md:border-t-0 sm:p-9">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-700"><svg viewBox="0 0 24 24" class="size-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>Pago protegido</div>
                    <h2 class="mt-7 text-lg font-bold text-slate-900">Resumen de tu suscripción</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Negocio</dt><dd class="text-right font-medium text-slate-800">{{ $tenant->nombre }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Administrador</dt><dd class="text-right font-medium text-slate-800">{{ $admin->name }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Correo</dt><dd class="max-w-44 break-all text-right font-medium text-slate-800">{{ $admin->email }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Frecuencia</dt><dd class="text-right font-medium capitalize text-slate-800">{{ $plan->periodicidad }}</dd></div>
                    </dl>
                    <div class="mt-7 rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-900"><strong>Al conectar Mercado Pago:</strong> este paso abrirá su checkout oficial para completar y autorizar el pago de forma segura. Esta pantalla es únicamente una maqueta local.</div>
                    <div class="mt-6 flex items-center justify-center gap-2 text-xs font-semibold text-slate-500"><span class="grid size-7 place-items-center rounded-full bg-sky-100 text-sky-700">MP</span> Experiencia de pago Mercado Pago</div>
                </aside>
            </div>
        </div>
    </main>
</body>
</html>

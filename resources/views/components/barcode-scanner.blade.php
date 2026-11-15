@props([
    'model',
    'action',
    'label' => 'Código de barras o QR',
    'placeholder' => 'Escanea o escribe el código',
])

@php($scannerId = 'barcode-scanner-'.str_replace(['.', '[', ']'], '-', $model))

<div id="{{ $scannerId }}" class="space-y-2" data-barcode-scanner>
    <label for="{{ $scannerId }}-input" class="block text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</label>
    <div class="flex flex-col gap-2 sm:flex-row">
        <input id="{{ $scannerId }}-input" data-barcode-input autofocus type="search" wire:model="{{ $model }}" wire:keydown.enter.prevent="{{ $action }}" autocomplete="off" placeholder="{{ $placeholder }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-400" />
        <div class="flex shrink-0 gap-2">
            <button type="button" data-barcode-start class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800 sm:flex-none" aria-label="Abrir cámara para escanear">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h3l2-2h6l2 2h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z"/><circle cx="12" cy="13" r="3.5"/></svg> Cámara
            </button>
            <button type="button" data-barcode-stop class="hidden flex-1 items-center justify-center gap-2 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2.5 text-sm font-semibold text-rose-800 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200 sm:flex-none">Detener cámara</button>
            <button type="button" data-barcode-submit wire:click="{{ $action }}" wire:loading.attr="disabled" wire:target="{{ $action }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-700 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 disabled:opacity-60 sm:flex-none">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 5v4m0-4h4M3 19v-4m0 4h4M21 5v4m0-4h-4m4 14v-4m0 4h-4M7 8h2v3H7zm8 0h2v3h-2zM7 14h2v2H7zm5-6h2m-2 5v3m3-2h2"/></svg> Buscar
            </button>
        </div>
    </div>
    <video data-barcode-video class="hidden max-h-64 w-full rounded-xl border border-slate-300 bg-black object-cover dark:border-slate-700" playsinline muted></video>
    <p data-barcode-status role="status" aria-live="polite" class="text-xs leading-5 text-slate-600 dark:text-slate-300">Admite lector USB/Bluetooth (incluidos QR) o la cámara del dispositivo.</p>
</div>

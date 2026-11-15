(() => {
    // Indicador global para navegaciones Livewire y enlaces internos de página completa.
    const loader = () => document.getElementById('multiverse-page-loader');
    let loaderTimer;

    function showPageLoader() {
        clearTimeout(loaderTimer);
        loaderTimer = setTimeout(() => {
            const element = loader();
            if (!element) return;
            element.classList.add('is-visible');
            element.setAttribute('aria-hidden', 'false');
        }, 140);
    }

    function hidePageLoader() {
        clearTimeout(loaderTimer);
        const element = loader();
        if (!element) return;
        element.classList.remove('is-visible');
        element.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('livewire:navigate', showPageLoader);
    document.addEventListener('livewire:navigated', hidePageLoader);
    window.addEventListener('pageshow', hidePageLoader);
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element) || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || link.hasAttribute('download') || link.hasAttribute('data-no-page-loader')) return;

        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.href === window.location.href || (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash)) return;
        showPageLoader();
    }, true);

    const activeScanners = new WeakMap();
    const formats = ['qr_code', 'ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'itf', 'data_matrix', 'pdf417', 'aztec'];

    function stopScanner(root, message = 'Cámara detenida.') {
        const scanner = activeScanners.get(root);
        if (scanner) {
            scanner.stream.getTracks().forEach((track) => track.stop());
            scanner.video.srcObject = null;
            scanner.video.classList.add('hidden');
            activeScanners.delete(root);
        }

        const status = root.querySelector('[data-barcode-status]');
        root.querySelector('[data-barcode-start]')?.classList.remove('hidden');
        root.querySelector('[data-barcode-stop]')?.classList.add('hidden');
        if (status) status.textContent = message;
    }

    async function startScanner(root) {
        const video = root.querySelector('[data-barcode-video]');
        const status = root.querySelector('[data-barcode-status]');

        if (!('BarcodeDetector' in window)) {
            status.textContent = 'Este navegador no puede escanear con cámara. Usa un lector USB/Bluetooth o escribe el código.';
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            status.textContent = 'La cámara requiere localhost o una conexión HTTPS. Puedes usar un lector o escribir el código.';
            return;
        }

        try {
            const supported = await window.BarcodeDetector.getSupportedFormats();
            const detector = new window.BarcodeDetector({ formats: formats.filter((format) => supported.includes(format)) });
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            video.srcObject = stream;
            video.classList.remove('hidden');
            root.querySelector('[data-barcode-start]')?.classList.add('hidden');
            root.querySelector('[data-barcode-stop]')?.classList.remove('hidden');
            await video.play();
            status.textContent = 'Apunta la cámara al código. Se buscará automáticamente.';
            activeScanners.set(root, { detector, stream, video, awaitingClear: false });

            const scan = async () => {
                if (!root.isConnected || !activeScanners.has(root)) return;

                try {
                    const [result] = await detector.detect(video);
                    const scanner = activeScanners.get(root);
                    if (result?.rawValue && scanner && !scanner.awaitingClear) {
                        scanner.awaitingClear = true;
                        const input = root.querySelector('input[wire\\:model]');
                        input.value = result.rawValue;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        status.textContent = `Código detectado: ${result.rawValue}. Retira el artículo para escanear el siguiente.`;
                        root.querySelector('[data-barcode-submit]')?.click();
                    } else if (!result?.rawValue && scanner) {
                        scanner.awaitingClear = false;
                    }
                } catch (error) {
                    stopScanner(root, 'No se pudo leer el código. Intenta con más luz o escribe el código.');
                    return;
                }

                requestAnimationFrame(scan);
            };

            requestAnimationFrame(scan);
        } catch (error) {
            stopScanner(root, error.name === 'NotAllowedError' ? 'Da permiso de cámara al navegador para escanear.' : 'No se pudo iniciar la cámara. Usa un lector o escribe el código.');
        }
    }

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const start = event.target.closest('[data-barcode-start]');
        if (start) {
            const root = start.closest('[data-barcode-scanner]');
            if (root) startScanner(root);
            return;
        }

        const stop = event.target.closest('[data-barcode-stop]');
        if (stop) {
            const root = stop.closest('[data-barcode-scanner]');
            if (root) stopScanner(root);
        }
    });

    document.addEventListener('livewire:navigating', () => {
        document.querySelectorAll('[data-barcode-scanner]').forEach((root) => stopScanner(root));
    });

    const focusBarcodeInput = () => {
        const input = document.querySelector('[data-barcode-input]');
        if (input && !input.disabled) input.focus({ preventScroll: true });
    };
    document.addEventListener('livewire:navigated', () => setTimeout(focusBarcodeInput, 50));
    document.addEventListener('barcode-ready', () => setTimeout(focusBarcodeInput, 50));
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', focusBarcodeInput, { once: true });
    else setTimeout(focusBarcodeInput, 50);
})();

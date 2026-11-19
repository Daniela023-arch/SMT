(() => {
    // Indicador global para peticiones, cargas, navegaciones y formularios.
    const loader = () => document.getElementById('multiverse-page-loader');
    let loaderTimer;
    let pendingWaits = 0;

    function revealPageLoader() {
        const element = loader();
        if (!element) return;
        element.classList.add('is-visible');
        element.setAttribute('aria-hidden', 'false');
    }

    function concealPageLoader() {
        clearTimeout(loaderTimer);
        const element = loader();
        if (!element) return;
        element.classList.remove('is-visible');
        element.setAttribute('aria-hidden', 'true');
    }

    function beginPageWait() {
        pendingWaits += 1;
        if (pendingWaits !== 1) return;

        //Hace que la opcion "cargando" se muestre después de 140ms
        clearTimeout(loaderTimer);
        loaderTimer = setTimeout(() => {
            if (pendingWaits > 0) revealPageLoader();
        }, 140);
    }

    function finishPageWait() {
        pendingWaits = Math.max(0, pendingWaits - 1);
        if (pendingWaits === 0) concealPageLoader();
    }

    function resetPageWaits() {
        pendingWaits = 0;
        concealPageLoader();
    }

    function installLivewireLoaderHook() {
        if (!window.Livewire || window.__multiverseLoaderHookInstalled) return;
        window.__multiverseLoaderHookInstalled = true;

        window.Livewire.hook('request', ({ succeed, fail }) => {
            beginPageWait();
            let finished = false;
            const finish = () => {
                if (finished) return;
                finished = true;
                finishPageWait();
            };
            succeed(finish);
            fail(finish);
        });
    }

    document.addEventListener('livewire:init', installLivewireLoaderHook);
    installLivewireLoaderHook();
    document.addEventListener('livewire:navigate', beginPageWait);
    document.addEventListener('livewire:navigated', finishPageWait);
    window.addEventListener('pageshow', resetPageWaits);

    for (const eventName of ['livewire-upload-start']) {
        document.addEventListener(eventName, beginPageWait, true);
    }
    for (const eventName of ['livewire-upload-finish', 'livewire-upload-error', 'livewire-upload-cancel']) {
        document.addEventListener(eventName, finishPageWait, true);
    }

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element) || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('wire:navigate') || link.target === '_blank' || link.hasAttribute('download') || link.hasAttribute('data-no-page-loader')) return;

        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.href === window.location.href || (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash)) return;
        beginPageWait();
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
        if ([...form.attributes].some(({ name }) => name.startsWith('wire:submit'))) return;
        beginPageWait();
    }, true);

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const toggle = event.target.closest('[data-password-toggle]');
        if (!toggle) return;

        const input = document.getElementById(toggle.getAttribute('data-password-toggle'));
        if (!(input instanceof HTMLInputElement)) return;

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(showPassword));
        toggle.setAttribute('aria-label', showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
        toggle.querySelector('[data-password-icon="show"]')?.classList.toggle('hidden', showPassword);
        toggle.querySelector('[data-password-icon="hide"]')?.classList.toggle('hidden', !showPassword);
        input.focus({ preventScroll: true });
    });

    function animateTenantGrowth() {
        const line = document.querySelector('[data-tenant-growth-line]');
        const area = document.querySelector('[data-tenant-growth-area]');
        const points = [...document.querySelectorAll('[data-tenant-growth-point]')];
        if (!line || !area || !points.length) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            line.style.strokeDasharray = 'none';
            area.style.opacity = '1';
            points.forEach((point) => { point.style.opacity = '1'; });
            return;
        }

        // Mantiene el trazo, el relleno y cada punto dentro del mismo recorrido visual.
        const length = line.getTotalLength();
        const duration = 1500;
        line.style.strokeDasharray = `${length}`;
        line.style.strokeDashoffset = `${length}`;
        line.animate(
            [{ opacity: 0, strokeDashoffset: length }, { opacity: 1, strokeDashoffset: 0 }],
            { duration, easing: 'cubic-bezier(.45,0,.2,1)', fill: 'forwards' },
        );
        area.animate(
            [{ opacity: 0 }, { opacity: 1 }],
            { duration, easing: 'cubic-bezier(.45,0,.2,1)', fill: 'forwards' },
        );
        points.forEach((point, index) => {
            const progress = points.length > 1 ? index / (points.length - 1) : 0.5;
            point.animate(
                [
                    { opacity: 0, transform: 'translateY(5px) scale(.55)' },
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                ],
                { duration: 360, delay: progress * (duration - 300), easing: 'cubic-bezier(.2,.8,.2,1)', fill: 'forwards' },
            );
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', animateTenantGrowth, { once: true });
    } else {
        requestAnimationFrame(animateTenantGrowth);
    }
    document.addEventListener('livewire:navigated', () => requestAnimationFrame(animateTenantGrowth));

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

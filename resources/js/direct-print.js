function getLabelPrinterName() {
    const meta = document.querySelector('meta[name="label-printer"]');

    return meta?.content || 'Xprinter XP-246B';
}

function extractDirectPrintEvent(event) {
    if (Array.isArray(event)) {
        return event[0] ?? {};
    }

    if (event?.detail && typeof event.detail === 'object') {
        return event.detail;
    }

    return event ?? {};
}

let isPrinting = false;
let lastPrintKey = '';
let lastPrintTime = 0;
let qzSecurityConfigured = false;
let qzConnectPromise = null;

function configureQzSecurity() {
    if (qzSecurityConfigured || !document.querySelector('meta[name="qz-signing-enabled"]')) {
        return;
    }

    const certUrl = document.querySelector('meta[name="qz-certificate-url"]')?.content;
    const signUrl = document.querySelector('meta[name="qz-sign-url"]')?.content;

    if (!certUrl || !signUrl) {
        return;
    }

    qz.security.setCertificatePromise(function (resolve, reject) {
        fetch(certUrl, { cache: 'no-store', credentials: 'same-origin' })
            .then(function (response) {
                return response.ok ? response.text() : Promise.reject(response.statusText);
            })
            .then(resolve)
            .catch(reject);
    });

    qz.security.setSignatureAlgorithm('SHA512');

    qz.security.setSignaturePromise(function (toSign) {
        return function (resolve, reject) {
            fetch(signUrl + '?data=' + encodeURIComponent(toSign), {
                cache: 'no-store',
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.ok ? response.text() : Promise.reject(response.statusText);
                })
                .then(resolve)
                .catch(reject);
        };
    });

    qzSecurityConfigured = true;
}

async function ensureQzConnected() {
    if (typeof qz === 'undefined') {
        throw new Error('QZ Tray غير محمّل. حدّث الصفحة وتأكد أن QZ Tray يعمل.');
    }

    configureQzSecurity();

    if (qz.websocket.isActive()) {
        return;
    }

    if (!qzConnectPromise) {
        qzConnectPromise = qz.websocket.connect().finally(function () {
            qzConnectPromise = null;
        });
    }

    await qzConnectPromise;
}

async function getPrintConfig() {
    await ensureQzConnected();

    const printerName = getLabelPrinterName();
    const printers = await qz.printers.find();

    if (!printers.includes(printerName)) {
        throw new Error(
            'الطابعة «' + printerName + '» غير موجودة. المتاح: ' + printers.join(', ')
        );
    }

    return qz.configs.create(printerName);
}

async function printPdfBase64(base64Payload) {
    const config = await getPrintConfig();

    await qz.print(config, [{
        type: 'pixel',
        format: 'pdf',
        flavor: 'base64',
        data: base64Payload,
    }]);
}

async function handleDirectPrint(event) {
    if (isPrinting) {
        return;
    }

    const data = extractDirectPrintEvent(event);
    const payload = data.payload;
    const count = data.count ?? 1;

    if (!payload) {
        return;
    }

    const dedupeKey = 'pdf:' + payload.slice(0, 64);

    if (dedupeKey === lastPrintKey && (Date.now() - lastPrintTime) < 5000) {
        return;
    }

    lastPrintKey = dedupeKey;
    lastPrintTime = Date.now();
    isPrinting = true;

    try {
        await printPdfBase64(payload);

        Livewire.dispatch('direct-print-labels-done', { count });
    } catch (error) {
        window.alert('فشلت الطباعة المباشرة: ' + error.message);
    } finally {
        isPrinting = false;
    }
}

if (!window.__directPrintRegistered) {
    window.__directPrintRegistered = true;

    document.addEventListener('livewire:init', () => {
        Livewire.on('direct-print-labels', handleDirectPrint);
    });
}

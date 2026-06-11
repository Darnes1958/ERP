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

let qzConnected = false;
let isPrinting = false;

async function ensureQzConnected() {
    if (typeof qz === 'undefined') {
        throw new Error('QZ Tray غير محمّل. حدّث الصفحة وتأكد أن QZ Tray يعمل.');
    }

    if (!qz.websocket.isActive()) {
        await qz.websocket.connect();
        qzConnected = true;
    }
}

async function printRawBase64(base64Payload) {
    await ensureQzConnected();

    const printerName = getLabelPrinterName();
    const printers = await qz.printers.find();

    if (!printers.includes(printerName)) {
        throw new Error(
            'الطابعة «' + printerName + '» غير موجودة. المتاح: ' + printers.join(', ')
        );
    }

    const config = qz.configs.create(printerName);

    await qz.print(config, [{
        type: 'raw',
        format: 'base64',
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

    isPrinting = true;

    try {
        await printRawBase64(payload);

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

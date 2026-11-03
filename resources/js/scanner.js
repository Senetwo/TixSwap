// Gate scanner: reads TixSwap QR codes from the camera and checks them.
import jsQR from 'jsqr';

const root = document.getElementById('scanner');
if (root) {
    const video = root.querySelector('video');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const panel = root.querySelector('[data-result]');
    const recent = root.querySelector('[data-recent]');
    const cameraMsg = root.querySelector('[data-camera-msg]');
    const eventSelect = root.querySelector('[data-event]');
    const manual = root.querySelector('[data-manual]');
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const detector = 'BarcodeDetector' in window ? new window.BarcodeDetector({ formats: ['qr_code'] }) : null;

    let busy = false;
    let last = { code: null, at: 0 };

    const styles = {
        admitted: ['bg-green-600', '✓', 'Admit'],
        already_used: ['bg-amber-500', '!', 'Already used'],
        wrong_event: ['bg-amber-500', '!', 'Wrong event'],
        blocked: ['bg-red-600', '✕', 'Do not admit'],
        invalid: ['bg-red-600', '✕', 'Invalid ticket'],
        error: ['bg-gray-700', '?', 'Try again'],
    };

    function show(data) {
        const [bg, icon, label] = styles[data.result] || styles.error;
        panel.className = 'rounded-3xl p-6 text-center text-white shadow-lift transition ' + bg;
        panel.innerHTML = `<div class="text-6xl font-black leading-none">${icon}</div>
            <p class="mt-3 text-2xl font-bold">${label}</p>
            <p class="mt-1 text-white/90">${data.message ?? ''}</p>
            ${data.ticket ? `<p class="mt-3 text-sm text-white/80">${data.ticket.event ?? ''} · ${data.ticket.seat ?? ''}<br>${data.ticket.holder ?? ''}</p>` : ''}`;
        navigator.vibrate?.(data.result === 'admitted' ? 120 : [80, 60, 80, 60, 200]);

        const li = document.createElement('li');
        li.className = 'flex items-center justify-between gap-3 py-2';
        li.innerHTML = `<span class="truncate">${data.ticket?.holder ?? 'Unknown'} · ${data.ticket?.seat ?? ''}</span><span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold text-white ${bg}">${label}</span>`;
        recent.prepend(li);
        while (recent.children.length > 6) recent.lastChild.remove();
    }

    async function check(code) {
        const now = Date.now();
        if (busy || (code === last.code && now - last.at < 5000)) return;
        busy = true;
        last = { code, at: now };
        try {
            const res = await fetch(root.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ code, event_id: eventSelect?.value || null }),
            });
            show(res.ok ? await res.json() : { result: 'error', message: 'Could not check the ticket (' + res.status + ').' });
        } catch (e) {
            show({ result: 'error', message: 'No connection. Check the network and try again.' });
        }
        setTimeout(() => { busy = false; }, 1500);
    }

    async function scanFrame() {
        if (video.readyState === video.HAVE_ENOUGH_DATA && !busy) {
            let code = null;
            if (detector) {
                const found = await detector.detect(video).catch(() => []);
                code = found[0]?.rawValue ?? null;
            } else {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' })?.data ?? null;
            }
            if (code) check(code.trim());
        }
        requestAnimationFrame(scanFrame);
    }

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            video.srcObject = stream;
            await video.play();
            cameraMsg.hidden = true;
            requestAnimationFrame(scanFrame);
        } catch (e) {
            cameraMsg.hidden = false;
        }
    }

    manual.addEventListener('submit', (e) => {
        e.preventDefault();
        const input = manual.querySelector('input');
        if (input.value.trim()) { last = { code: null, at: 0 }; check(input.value.trim()); input.value = ''; }
    });

    startCamera();
}

import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Installable app + offline tickets (see public/sw.js).
if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));

    // Don't leave saved tickets on a shared phone after logging out.
    document.addEventListener('submit', (e) => {
        if (e.target.action && new URL(e.target.action).pathname === '/logout') {
            navigator.serviceWorker.controller?.postMessage('clear-tickets');
            window.caches?.delete('tixswap-tickets');
        }
    });
}

// "Install app" button (shown where the browser supports it).
let installPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    installPrompt = e;
    document.querySelectorAll('[data-install-app]').forEach((el) => { el.hidden = false; });
});
document.addEventListener('click', async (e) => {
    const button = e.target.closest('[data-install-app] button');
    if (!button || !installPrompt) return;
    installPrompt.prompt();
    await installPrompt.userChoice;
    installPrompt = null;
    document.querySelectorAll('[data-install-app]').forEach((el) => { el.hidden = true; });
});

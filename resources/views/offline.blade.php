{{-- Cached by the service worker, so it must not depend on who's logged in. --}}
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <x-partials-head title="You're offline" />
    <meta name="robots" content="noindex">
</head>
<body class="flex min-h-full flex-col items-center justify-center bg-gray-50 px-6 text-center">
    <x-application-logo class="h-10 w-10 text-brand-600" />
    <span class="mt-10 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 8.735 8.735m0 0a.374.374 0 1 1 .53.53m-.53-.53.53.53m0 0L21 21M14.652 9.348a3.75 3.75 0 0 1 0 5.304m2.121-7.425a6.75 6.75 0 0 1 0 9.546m2.121-11.667c3.808 3.807 3.808 9.98 0 13.788m-9.546-4.242a3.733 3.733 0 0 1-1.06-2.122m-1.061 4.243a6.75 6.75 0 0 1-1.625-6.929m-.496 9.05c-3.068-3.067-3.664-7.67-1.79-11.334M12 12h.008v.008H12V12Z" /></svg>
    </span>
    <h1 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">You're offline</h1>
    <p class="mt-2 max-w-sm text-gray-600">Tickets you've opened before are saved on this phone. Open <strong>My tickets</strong> to show yours at the gate.</p>
    <div class="mt-8 flex flex-col items-center gap-3">
        <x-ui.button href="/my-tickets">My tickets</x-ui.button>
        <button type="button" onclick="location.reload()" class="text-sm font-medium text-gray-500 hover:text-gray-900">Try again</button>
    </div>
</body>
</html>

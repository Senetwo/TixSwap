<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <x-partials-head title="Gate scanner" />
    <meta name="robots" content="noindex">
    @vite('resources/js/scanner.js')
</head>
<body class="min-h-full bg-gray-950 text-white">
    <div id="scanner" data-url="{{ route('scan.check') }}" class="mx-auto flex min-h-screen max-w-md flex-col gap-4 px-4 py-4">
        <header class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold"><x-application-logo class="h-7 w-7 text-brand-500" /> Gate scanner</a>
            <span class="text-sm text-gray-400">{{ auth()->user()->name }}</span>
        </header>

        <label class="block">
            <span class="sr-only">Event</span>
            <select data-event class="block w-full rounded-xl border-0 bg-white/10 py-3 text-white ring-1 ring-white/15 focus:ring-2 focus:ring-white/50">
                <option value="" class="text-gray-900">Any event</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" class="text-gray-900" @selected($eventId === $event->id)>{{ $event->name }} · {{ $event->date->format('D j M, g:i A') }}</option>
                @endforeach
            </select>
        </label>

        <div class="relative aspect-square overflow-hidden rounded-3xl bg-black ring-1 ring-white/10">
            <video class="h-full w-full object-cover" playsinline muted></video>
            <div class="pointer-events-none absolute inset-[18%] rounded-2xl border-4 border-white/70 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]" aria-hidden="true"></div>
            <p data-camera-msg hidden class="absolute inset-0 flex items-center justify-center bg-gray-950/90 p-8 text-center text-gray-300">
                Camera unavailable. Allow camera access, or type the code below.
            </p>
        </div>

        <div data-result class="rounded-3xl bg-white/5 p-6 text-center ring-1 ring-white/10" aria-live="assertive">
            <p class="text-lg font-semibold">Point the camera at a ticket's QR code</p>
            <p class="mt-1 text-sm text-gray-400">Each ticket can only be admitted once.</p>
        </div>

        <form data-manual class="flex gap-2">
            <label for="manual-code" class="sr-only">Ticket code</label>
            <input id="manual-code" type="text" autocomplete="off" autocapitalize="off" placeholder="Or type the code under the QR"
                   class="block w-full rounded-xl border-0 bg-white/10 py-3 font-mono text-sm text-white ring-1 ring-white/15 placeholder:font-sans placeholder:text-gray-500 focus:ring-2 focus:ring-white/50">
            <button class="rounded-xl bg-white px-4 font-semibold text-gray-900">Check</button>
        </form>

        <section>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Recent scans</h2>
            <ul data-recent class="mt-1 divide-y divide-white/10 text-sm text-gray-300"></ul>
        </section>
    </div>
</body>
</html>

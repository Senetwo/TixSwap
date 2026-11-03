<x-tixswap-layout title="Complete your payment" width="max-w-lg">
    <div class="rounded-3xl bg-white p-6 text-center shadow-lift ring-1 ring-gray-200 sm:p-8">
        {{-- Waiting --}}
        <div id="state-pending" @class(['hidden' => ! $order->isPending()])>
            <div class="relative mx-auto h-20 w-20">
                <span class="absolute inset-0 animate-ping rounded-full bg-mpesa-500/20" aria-hidden="true"></span>
                <span class="relative flex h-20 w-20 items-center justify-center rounded-full bg-mpesa-50 text-mpesa-600 ring-8 ring-mpesa-50/60">
                    <x-ui.icon name="phone" class="h-9 w-9" />
                </span>
            </div>
            <h1 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">Check your phone</h1>
            <p class="mt-2 text-gray-600">
                We've sent an M-Pesa prompt{{ $maskedPhone ? ' to '.$maskedPhone : '' }}.
                Enter your PIN on your phone to pay <strong class="money text-gray-900">KES {{ number_format((int) $order->amount) }}</strong>.
            </p>

            <ol class="mx-auto mt-8 max-w-xs space-y-4 text-left text-sm" aria-label="Payment progress">
                <li class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-mpesa-600 text-white"><x-ui.icon name="check" class="h-4 w-4" /></span>
                    <span class="font-medium text-gray-900">Prompt sent to your phone</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full ring-2 ring-mpesa-600"><span class="h-2.5 w-2.5 animate-pulse rounded-full bg-mpesa-600"></span></span>
                    <span class="font-medium text-gray-900">Enter your M-Pesa PIN</span>
                </li>
                <li class="flex items-center gap-3 text-gray-400">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full ring-2 ring-gray-200"></span>
                    <span>Get your ticket</span>
                </li>
            </ol>

            <p class="mt-8 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600">
                <x-ui.icon name="clock" class="h-4 w-4" /> Ticket held for <span id="countdown" class="money font-semibold text-gray-900">--:--</span>
            </p>
        </div>

        {{-- Settled without success --}}
        <div id="state-done" @class(['hidden' => $order->isPending()])>
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-ui.icon name="x-mark" class="h-9 w-9" />
            </span>
            <h1 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">Payment not completed</h1>
        </div>

        <p id="status-message" class="mt-4 text-sm text-gray-500" role="status" aria-live="polite"></p>

        <div class="mt-8 space-y-3">
            <x-ui.button id="retry-link" variant="mpesa" size="lg" :href="route('tickets.show', $order->ticket_id)" @class(['w-full', 'hidden' => $order->isPending()])>
                Try again
            </x-ui.button>
            <a href="{{ route('dashboard') }}" class="block text-sm font-medium text-gray-500 hover:text-gray-900">Back to all tickets</a>
        </div>
    </div>

    <div class="mt-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-gray-200/70">
        <x-event-image :event="$order->ticket->event" class="h-14 w-14" rounded="rounded-xl" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-gray-900">{{ $order->ticket->event->name }}</p>
            <p class="truncate text-sm text-gray-500">{{ $order->ticket->seat_details }} · Order #{{ $order->id }}</p>
        </div>
        <p class="money font-semibold text-gray-900">KES {{ number_format((int) $order->amount) }}</p>
    </div>

    @push('scripts')
        <script>
            (function () {
                const statusUrl = @json(route('orders.status', $order));
                const expiresAt = @json(optional($order->expires_at)->toIso8601String());
                const pending = document.getElementById('state-pending');
                const done = document.getElementById('state-done');
                const message = document.getElementById('status-message');
                const retry = document.getElementById('retry-link');
                const countdown = document.getElementById('countdown');
                let finished = @json(! $order->isPending());

                function tick() {
                    if (!expiresAt || !countdown) return;
                    const left = Math.max(0, Math.floor((new Date(expiresAt) - new Date()) / 1000));
                    countdown.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
                }

                async function poll() {
                    if (finished) return;
                    try {
                        const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                        if (res.ok) {
                            const data = await res.json();
                            message.textContent = data.message;
                            if (data.redirect) { finished = true; window.location = data.redirect; return; }
                            if (data.status !== 'pending') {
                                finished = true;
                                pending.classList.add('hidden');
                                done.classList.remove('hidden');
                                if (data.status !== 'needs_refund') retry.classList.remove('hidden');
                                return;
                            }
                        }
                    } catch (e) { /* network blip: try again */ }
                    setTimeout(poll, 3000);
                }

                tick(); setInterval(tick, 1000);
                poll();
            })();
        </script>
    @endpush
</x-tixswap-layout>

<x-tixswap-layout>
    <x-slot name="title">Complete your payment</x-slot>

    <div class="bg-white rounded-2xl shadow-xl max-w-md mx-auto p-8 text-center">
        <div id="state-pending" @class(['hidden' => ! $order->isPending()])>
            <div class="mx-auto h-16 w-16 rounded-full border-4 border-green-200 border-t-green-600 animate-spin" aria-hidden="true"></div>
            <h1 class="mt-6 text-2xl font-bold text-gray-900">Check your phone</h1>
            <p class="mt-2 text-gray-600">
                We've sent an M-Pesa prompt{{ $maskedPhone ? ' to '.$maskedPhone : '' }}.
                Enter your PIN on your phone to pay
                <strong>KES {{ number_format((int) $order->amount) }}</strong>.
            </p>
            <p class="mt-4 text-sm text-gray-500">
                Ticket held for you for <span id="countdown" class="font-semibold">--:--</span>
            </p>
        </div>

        <div id="state-done" @class(['hidden' => $order->isPending()])>
            <div class="mx-auto h-16 w-16 rounded-full bg-red-100 flex items-center justify-center text-3xl" aria-hidden="true">!</div>
            <h1 class="mt-6 text-2xl font-bold text-gray-900">Payment not completed</h1>
        </div>

        <p id="status-message" class="mt-6 text-gray-700" role="status" aria-live="polite"></p>

        <div class="mt-8 space-y-3">
            <a id="retry-link" href="{{ route('tickets.show', $order->ticket_id) }}"
               @class(['block w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700', 'hidden' => $order->isPending()])>
                Try again
            </a>
            <a href="{{ route('dashboard') }}" class="block text-sm text-gray-500 hover:text-gray-700 underline">Back to all tickets</a>
        </div>

        <p class="mt-8 text-xs text-gray-400">{{ $order->ticket->event->name }} · Seat {{ $order->ticket->seat_details }} · Order #{{ $order->id }}</p>
    </div>

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
                if (!expiresAt) return;
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
</x-tixswap-layout>

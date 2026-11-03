<x-tixswap-layout>
    <x-slot name="title">Buy Ticket for {{ $ticket->event->name }}</x-slot>

    <div class="bg-white rounded-lg shadow-xl max-w-4xl mx-auto p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Ticket Image -->
            <div>
                <img class="rounded-lg object-cover w-full" src="{{ asset('storage/' . $ticket->event->image_url) }}" alt="{{ $ticket->event->name }}">
            </div>

            <!-- Ticket and Payment Details -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $ticket->event->name }}</h1>
                <p class="text-md text-gray-500 mt-2">{{ \Carbon\Carbon::parse($ticket->event->date)->format('l, F j, Y \a\t g:i A') }}</p>
                <p class="text-md text-gray-500">{{ $ticket->event->location }}</p>
                <p class="mt-4 text-lg font-semibold text-gray-800">Seat: {{ $ticket->seat_details }}</p>

                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span>Total Price:</span>
                        <span>KES {{ number_format((int) ceil($ticket->price)) }}</span>
                    </div>
                </div>

                @if ($reserved)
                    <div class="mt-8 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-yellow-800">
                        Someone is paying for this ticket right now. If they don't finish, it will be back on sale within {{ config('services.tixswap.reservation_minutes') }} minutes.
                    </div>
                @else
                    <form id="payment-form" action="{{ route('pay') }}" method="POST" class="mt-8 space-y-4">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">M-Pesa phone number</label>
                            <input type="tel" name="phone_number" id="phone_number" inputmode="tel" autocomplete="tel"
                                   value="{{ old('phone_number') }}" placeholder="0712 345 678" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('phone_number') border-red-500 @enderror">
                            @error('phone_number')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="pay-button" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 disabled:opacity-60">
                            Pay KES {{ number_format((int) ceil($ticket->price)) }} with M-Pesa
                        </button>

                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>📱 You'll get a prompt on your phone. Enter your M-Pesa PIN <strong>there</strong>. TixSwap will never ask for your PIN.</li>
                            <li>⏱️ The ticket is held for you for {{ config('services.tixswap.reservation_minutes') }} minutes while you pay.</li>
                            <li>🔒 Your money is held safely until after the event, then released to the seller.</li>
                        </ul>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Stop double submissions (each click would send another M-Pesa prompt).
        document.getElementById('payment-form')?.addEventListener('submit', function () {
            const button = document.getElementById('pay-button');
            button.disabled = true;
            button.textContent = 'Sending M-Pesa prompt…';
        });
    </script>
</x-tixswap-layout>

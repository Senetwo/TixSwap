<x-tixswap-layout>
    <x-slot name="title">Report a problem</x-slot>

    <div class="bg-white rounded-lg shadow-md max-w-2xl mx-auto p-8">
        <h1 class="text-2xl font-bold text-gray-900">Report a problem with your ticket</h1>
        <p class="mt-2 text-gray-600">
            {{ $order->ticket->event->name }} · Seat {{ $order->ticket->seat_details }} · KES {{ number_format($order->amount) }}
        </p>
        <p class="mt-4 rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
            Your payment is still held by TixSwap. Reporting a problem keeps it on hold until our team has reviewed it.
            If the ticket was not valid, we refund you to your M-Pesa number.
        </p>

        <form method="POST" action="{{ route('disputes.store', $order) }}" enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf

            <fieldset>
                <legend class="text-sm font-medium text-gray-700">What went wrong?</legend>
                <div class="mt-2 space-y-2">
                    @foreach ($reasons as $value => $label)
                        <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="reason" value="{{ $value }}" @checked(old('reason') === $value) required class="mt-1 text-red-600 focus:ring-red-500">
                            <span class="text-gray-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('reason') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            <div>
                <label for="details" class="block text-sm font-medium text-gray-700">Tell us what happened</label>
                <textarea id="details" name="details" rows="5" required minlength="20" maxlength="2000"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                          placeholder="e.g. At the gate the scanner said the ticket had already been used at 7:15pm.">{{ old('details') }}</textarea>
                @error('details') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="evidence" class="block text-sm font-medium text-gray-700">Evidence (optional)</label>
                <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-sm">
                <p class="mt-1 text-xs text-gray-500">A photo or screenshot helps, e.g. the scanner error or a message from the organiser. JPG, PNG or PDF, up to 4 MB.</p>
                @error('evidence') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('tickets.purchased.show', $order->ticket_id) }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                <button class="bg-red-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-red-700">Send report</button>
            </div>
        </form>
    </div>
</x-tixswap-layout>

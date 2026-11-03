<x-tixswap-layout title="Report a problem" width="max-w-2xl">
    <x-ui.page-header title="Report a problem" :back="route('tickets.purchased.show', $order->ticket_id)" back-label="Back to ticket"
        description="Tell us what went wrong. The seller's payment stays on hold while we look into it." />

    <div class="mb-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-gray-200/70">
        <x-event-image :event="$order->ticket->event" class="h-14 w-14" rounded="rounded-xl" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-gray-900">{{ $order->ticket->event->name }}</p>
            <p class="truncate text-sm text-gray-500">{{ $order->ticket->seat_details }}</p>
        </div>
        <p class="money font-semibold text-gray-900">KES {{ number_format($order->amount) }}</p>
    </div>

    <x-ui.card>
        <form method="POST" action="{{ route('disputes.store', $order) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <fieldset>
                <legend class="text-sm font-medium text-gray-800">What went wrong?</legend>
                <div class="mt-3 grid gap-2">
                    @foreach ($reasons as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-4 transition hover:bg-gray-50 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50 has-[:checked]:ring-1 has-[:checked]:ring-gray-900">
                            <input type="radio" name="reason" value="{{ $value }}" @checked(old('reason') === $value) required class="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900">
                            <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('reason') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            <x-ui.field label="What happened?" name="details" type="textarea" rows="5" required minlength="20" maxlength="2000"
                        placeholder="e.g. At gate B the scanner said the ticket had already been used, at about 7:15pm." />

            <x-ui.field label="Evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.pdf" optional
                        class="[&_input]:p-2 [&_input]:text-sm"
                        hint="A photo or screenshot helps, e.g. the scanner error or a message from the organiser. JPG, PNG or PDF, up to 4 MB." />

            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                <x-ui.button variant="secondary" :href="route('tickets.purchased.show', $order->ticket_id)">Cancel</x-ui.button>
                <x-ui.button>Send report</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-tixswap-layout>

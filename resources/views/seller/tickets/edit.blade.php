@php($fee = (float) config('services.tixswap.commission_percent'))
<x-tixswap-layout title="Edit listing" width="max-w-3xl">
    <x-ui.page-header title="Edit listing" :back="route('seller.dashboard')" back-label="My listings" />

    <div class="mb-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-gray-200/70">
        <x-event-image :event="$ticket->event" class="h-16 w-16" rounded="rounded-xl" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-gray-900">{{ $ticket->event->name }}</p>
            <p class="truncate text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($ticket->event->date)->format('D, M j, Y · g:i A') }} · {{ $ticket->event->location }}</p>
        </div>
        <x-ui.status :status="$ticket->status" />
    </div>

    @if ($ticket->status === 'approved')
        <x-ui.alert tone="info" class="mb-6">Saving changes takes your listing offline until our team has checked it again.</x-ui.alert>
    @endif

    <x-ui.card>
        <form action="{{ route('seller.tickets.update', $ticket) }}" method="POST" class="space-y-5"
              x-data="{ price: @js(old('price', (int) $ticket->price)), fee: {{ $fee }} }">
            @csrf
            @method('PATCH')
            <x-ui.field label="Seat or ticket type" name="seat_details" :value="$ticket->seat_details" required />
            <div>
                <x-ui.field label="Your price" name="price" type="number" prefix="KES" min="1" step="1" required inputmode="numeric" x-model="price" />
                <p class="mt-1.5 text-sm text-gray-500" x-show="price > 0">
                    You'll receive <strong class="money text-gray-900" x-text="'KES ' + Math.floor(Math.ceil(price) * (1 - fee / 100)).toLocaleString()"></strong> after the TixSwap fee.
                </p>
            </div>
            <x-ui.field label="Where you bought it" name="original_vendor" :value="$ticket->original_vendor === 'N/A' ? '' : $ticket->original_vendor" optional />
            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <x-ui.button variant="secondary" :href="route('seller.dashboard')">Cancel</x-ui.button>
                <x-ui.button>Save changes</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @can('delete', $ticket)
        <x-ui.card class="mt-6 ring-red-200" title="Remove listing" description="Take this ticket off TixSwap. This can't be undone.">
            <form action="{{ route('seller.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Remove this listing? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <x-ui.button variant="danger" icon="trash">Remove listing</x-ui.button>
            </form>
        </x-ui.card>
    @endcan
</x-tixswap-layout>

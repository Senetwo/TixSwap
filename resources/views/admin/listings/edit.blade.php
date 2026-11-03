<x-admin-layout title="Edit listing">
    <div class="max-w-2xl">
        <a href="{{ route('admin.listings.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> Listings
        </a>
        <div class="mb-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-gray-200/70">
            <x-event-image :event="$ticket->event" class="h-14 w-14" rounded="rounded-xl" />
            <div class="min-w-0 flex-1">
                <p class="truncate font-semibold text-gray-900">{{ $ticket->event->name }}</p>
                <p class="truncate text-sm text-gray-500">Listing #{{ $ticket->id }} · {{ $ticket->seller?->name }}</p>
            </div>
            <x-ui.status :status="$ticket->status" />
        </div>
        <x-ui.card>
            <form action="{{ route('admin.listings.update', $ticket) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <x-ui.field label="Seat or ticket type" name="seat_details" :value="$ticket->seat_details" required />
                <x-ui.field label="Price" name="price" type="number" prefix="KES" min="1" step="1" :value="(int) $ticket->price" required />
                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <x-ui.button variant="secondary" :href="route('admin.listings.index')">Cancel</x-ui.button>
                    <x-ui.button>Save changes</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-admin-layout>

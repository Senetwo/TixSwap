<x-admin-layout title="Listings">
    <x-ui.card padding="p-0">
        @if ($tickets->isEmpty())
            <x-ui.empty-state icon="ticket" title="No listings yet" class="border-0" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Event</th>
                            <th class="table-th">Seller</th>
                            <th class="table-th text-right">Price</th>
                            <th class="table-th">Status</th>
                            <th class="table-th"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50/60">
                                <td class="table-td">
                                    <div class="flex items-center gap-3">
                                        <x-event-image :event="$ticket->event" class="h-10 w-10" rounded="rounded-lg" />
                                        <div class="min-w-0">
                                            <p class="max-w-xs truncate font-medium text-gray-900">{{ $ticket->event->name }}</p>
                                            <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($ticket->event->date)->format('M j, Y') }} · {{ $ticket->seat_details }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-td whitespace-nowrap">{{ $ticket->seller?->name }}</td>
                                <td class="table-td money whitespace-nowrap text-right font-medium text-gray-900">KES {{ number_format($ticket->price) }}</td>
                                <td class="table-td"><x-ui.status :status="$ticket->status" /></td>
                                <td class="table-td whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-1">
                                        @if (! in_array($ticket->status, ['sold', 'reserved']))
                                            <x-ui.button variant="ghost" size="sm" :href="route('admin.listings.edit', $ticket)">Edit</x-ui.button>
                                        @endif
                                        @can('delete', $ticket)
                                            <form action="{{ route('admin.listings.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600" aria-label="Delete listing {{ $ticket->id }}"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</x-admin-layout>

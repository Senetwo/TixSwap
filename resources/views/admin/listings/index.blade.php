<x-admin-layout>
    <x-slot name="title">All Ticket Listings</x-slot>
    <h1 class="text-2xl font-bold mb-6">All Ticket Listings</h1>
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $ticket->event->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $ticket->seller->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-bold rounded-full text-white
                                @if($ticket->status == 'approved') bg-green-500
                                @elseif($ticket->status == 'pending') bg-yellow-500
                                @else bg-red-500 @endif">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <form action="{{ route('admin.listings.destroy', $ticket) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
<!-- In resources/views/admin/tickets/pending.blade.php -->
<x-admin-layout>
    <x-slot name="title">Pending Ticket Approvals</x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Review Pending Tickets</h1>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($tickets as $ticket)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $ticket->seller->name }}</div>
                            <div class="text-sm text-gray-500">{{ $ticket->seller->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $ticket->event->name }}</div>
                            <div class="text-sm text-gray-500">Seat: {{ $ticket->seat_details }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">KES {{ number_format($ticket->price) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($ticket->purchase_proof) }}" target="_blank" class="text-blue-600 hover:text-blue-900 hover:underline">View Proof</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <form action="{{ route('admin.tickets.approve', $ticket) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                            </form>
                            <form action="{{ route('admin.tickets.reject', $ticket) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <textarea name="rejection_reason" rows="2" class="w-full border rounded-md p-1 text-xs" placeholder="Reason for rejection..."></textarea>
                                <button type="submit" class="w-full text-red-600 hover:text-red-900 mt-1">Reject</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            There are no pending tickets to review.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
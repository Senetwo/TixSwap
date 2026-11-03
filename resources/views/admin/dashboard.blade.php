<x-admin-layout>
    <x-slot name="title">Admin Dashboard</x-slot>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Sales Overview</h3>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Tickets by Status</h3>
            <canvas id="ticketsChart" class="mx-auto" style="max-width: 250px;"></canvas>
        </div>
    </div>

    <!-- Current Listings Table -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">Current Listings</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($allTickets as $ticket)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $ticket->event->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $ticket->seller->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">KES {{ number_format($ticket->price) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                 <span class="px-3 py-1 text-xs font-bold rounded-full text-white
                                    @if($ticket->status == 'approved') bg-green-500
                                    @elseif($ticket->status == 'pending') bg-yellow-500
                                    @else bg-red-500 @endif">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.tickets.pending') }}" class="mt-4 inline-block bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600">Review Tickets</a>
                                <a href="{{ route('admin.listings.edit', $ticket) }}" class="bg-red-600 text-white py-1 px-3 rounded-md font-semibold hover:bg-red-700 text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">No tickets have been listed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Chart.js script for Sales Overview
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['Total Sales'],
                datasets: [{
                    label: 'Sales (KES)',
                    data: [{{ $stats['totalSalesValue'] ?? 0 }}],
                    backgroundColor: 'rgba(220, 38, 38, 0.8)',
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Chart.js script for Tickets Sold
        const ticketsCtx = document.getElementById('ticketsChart').getContext('2d');
        new Chart(ticketsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sold', 'Available', 'Pending'],
                datasets: [{
                    label: 'Tickets',
                    data: [{{ $stats['ticketsSold'] }}, {{ $stats['activeListings'] }}, {{ $stats['pendingTickets'] }}],
                    backgroundColor: ['rgb(220, 38, 38)','rgb(22, 163, 74)','rgb(255, 205, 86)'],
                    hoverOffset: 4
                }]
            }
        });
    </script>
</x-admin-layout>

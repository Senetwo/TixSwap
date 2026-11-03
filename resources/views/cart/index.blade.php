<x-tixswap-layout>
    <x-slot name="title">My Tickets</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('dashboard') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Home</a>
        <a href="{{ route('cart.index') }}" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">My Tickets</a>
        <a href="{{ route('support') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Support</a>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Purchased Tickets</h1>

        <div class="space-y-6">
            @forelse ($tickets as $ticket)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="flex items-center">
                        <!-- Event Image -->
                        <div class="flex-shrink-0">
                            <img class="h-32 w-32 object-cover" src="{{ asset('storage/' . $ticket->event->image_url) }}" alt="{{ $ticket->event->name }}">
                        </div>
                        <!-- Ticket Details -->
                        <div class="p-4 flex-grow flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">{{ $ticket->event->name }}</h2>
                                <p class="text-sm text-gray-600">Seat: {{ $ticket->seat_details }}</p>
                                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y') }}</p>
                            </div>
                            <!-- Action Button -->
                            <div class="flex-shrink-0 ml-4">
                                <a href="{{ route('tickets.purchased.show', $ticket) }}" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 text-sm whitespace-nowrap">
                                    View Ticket & QR Code
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-bold">You haven't purchased any tickets yet.</h3>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-block bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">Browse Events</a>
                </div>
            @endforelse
        </div>
    </div>
</x-tixswap-layout>

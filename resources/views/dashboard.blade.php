<x-tixswap-layout>
    <x-slot name="title">Buyer Dashboard</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('dashboard') }}" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">Home</a>
        <a href="{{ route('cart.index') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">My Tickets</a>
        <a href="{{ route('support') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Support</a>
    </x-slot>

    <!-- Search Bar -->
    <div class="mb-8">
        <form action="{{ route('dashboard') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Search for events by name or location..." class="w-full p-3 border border-gray-300 rounded-lg" value="{{ request('search') }}">
            <button type="submit" class="bg-red-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-red-700">Search</button>
        </form>
    </div>

    <!-- All Tickets Section -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">All Available Tickets</h2>
        </div>

        @if ($tickets->isEmpty())
            <div class="text-center py-16 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-bold">No tickets found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($tickets as $ticket)
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <img class="h-48 w-full object-cover" src="{{ asset('storage/' . $ticket->event->image_url) }}" alt="{{ $ticket->event->name }}">
                    <div class="p-6">
                        <h3 class="text-xl font-bold">{{ $ticket->event->name }}</h3>
                        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y') }} - {{ $ticket->event->location }}</p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">KES {{ number_format($ticket->price) }}</p>
                        <a href="{{ route('tickets.show', $ticket) }}" class="mt-4 w-full block text-center bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700">Buy Now</a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-tixswap-layout>
<x-tixswap-layout>
    <x-slot name="title">Seller Dashboard</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('seller.dashboard') }}" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">Dashboard</a>
        <a href="{{ route('seller.tickets.create') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">List a New Ticket</a>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Your Ticket Listings</h1>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if ($tickets->isEmpty())
            <div class="text-center py-16 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-bold">You have not listed any tickets yet.</h3>
                <a href="{{ route('seller.tickets.create') }}" class="mt-4 inline-block bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">List Your First Ticket</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($tickets as $ticket)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                        <div class="relative">
                            <img class="h-48 w-full object-cover" src="{{ asset('storage/' . $ticket->event->image_url) }}" alt="{{ $ticket->event->name }}">
                            <div class="absolute top-2 right-2">
                                @if($ticket->status === 'pending')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($ticket->status === 'approved')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @elseif($ticket->status === 'sold')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Sold
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold">{{ $ticket->event->name }}</h3>
                            <p class="text-sm text-gray-600">Seat: {{ $ticket->seat_details }}</p>
                            <p class="mt-2 text-lg font-semibold text-gray-800">KES {{ number_format($ticket->price) }}</p>
                            <p class="text-xs text-gray-400 mt-1">Listed on {{ $ticket->created_at->format('M d, Y') }}</p>
                            
                            <div class="mt-4 pt-4 border-t">
                                @if($ticket->status !== 'sold')
                                    <a href="{{-- route('seller.tickets.edit', $ticket) --}}" class="w-full block text-center bg-gray-200 text-gray-800 py-2 rounded-lg font-semibold hover:bg-gray-300">Edit Listing</a>
                                @else
                                    <div class="text-center text-gray-500 font-semibold">This ticket has been sold.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-tixswap-layout>

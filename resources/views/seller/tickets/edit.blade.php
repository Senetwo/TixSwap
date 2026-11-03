<x-tixswap-layout>
    <x-slot name="title">Edit Ticket</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('seller.dashboard') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">My Listings</a>
        <a href="{{ route('seller.tickets.create') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Sell a Ticket</a>
    </x-slot>

    <div class="bg-white shadow-md rounded-lg p-8 max-w-4xl mx-auto">
        <!-- Main Update Form -->
        <form action="{{ route('seller.tickets.update', $ticket) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <h2 class="text-2xl font-bold mb-6">Edit Your Listing for: {{ $ticket->event->name }}</h2>

            <!-- Event Details (Read-only) -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Event Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p><strong class="text-gray-600">Event:</strong> {{ $ticket->event->name }}</p>
                    <p><strong class="text-gray-600">Location:</strong> {{ $ticket->event->location }}</p>
                    <p><strong class="text-gray-600">Date:</strong> {{ \Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y g:i A') }}</p>
                </div>
            </div>

            <!-- Editable Ticket Details -->
            <div class="pt-6 border-t">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Your Ticket Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="seat_details" class="block text-sm font-medium text-gray-700">Seat Details (e.g., Section A, Row 5)</label>
                        <input type="text" id="seat_details" name="seat_details" value="{{ old('seat_details', $ticket->seat_details) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 @error('seat_details') border-red-500 @enderror">
                        @error('seat_details')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Your Selling Price (KES)</label>
                        <input type="number" id="price" name="price" value="{{ old('price', $ticket->price) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 @error('price') border-red-500 @enderror">
                         @error('price')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                 <div class="mt-6">
                    <label for="original_vendor" class="block text-sm font-medium text-gray-700">Original Vendor (e.g., Ticketmaster)</label>
                    <input type="text" id="original_vendor" name="original_vendor" value="{{ old('original_vendor', $ticket->original_vendor) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 @error('original_vendor') border-red-500 @enderror">
                     @error('original_vendor')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Update Button (Inside the main form) -->
            <div class="flex items-center justify-end pt-6 border-t">
                <div class="flex gap-4">
                     <a href="{{ route('seller.dashboard') }}" class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700">Update Ticket</button>
                </div>
            </div>
        </form>

        <!-- Delete Form (Separate from the update form) -->
        <div class="mt-6 border-t pt-6 text-left">
             <form action="{{ route('seller.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 font-medium underline">Delete This Listing</button>
            </form>
        </div>
    </div>
</x-tixswap-layout>
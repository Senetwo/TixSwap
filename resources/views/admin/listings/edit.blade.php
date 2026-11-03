<x-admin-layout>
    <x-slot name="title">Edit Listing #{{ $ticket->id }}</x-slot>
    <h1 class="text-2xl font-bold mb-6">Edit Listing for {{ $ticket->event->name }}</h1>
    <form action="{{ route('admin.listings.update', $ticket) }}" method="POST" class="bg-white p-6 rounded-lg shadow space-y-6">
        @csrf
        @method('PATCH')
        <p><strong>Seller:</strong> {{ $ticket->seller->name }}</p>
        <div>
            <label for="seat_details" class="block text-sm font-medium text-gray-700">Seat Details</label>
            <input type="text" name="seat_details" id="seat_details" value="{{ old('seat_details', $ticket->seat_details) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price (KES)</label>
            <input type="number" name="price" id="price" value="{{ old('price', $ticket->price) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancel</a>
            <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Update Listing</button>
        </div>
    </form>
</x-admin-layout>
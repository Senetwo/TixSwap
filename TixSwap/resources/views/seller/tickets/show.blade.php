<x-tixswap-layout>
    <x-slot name="title">Purchase Ticket for {{ $ticket->event->name }}</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('dashboard') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Home</a>
        <a href="#" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">My Tickets</a>
        <a href="#" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Support</a>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-8">
            <!-- Event Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <img src="{{ Storage::url($ticket->event->image_url) }}" alt="{{ $ticket->event->name }}" class="w-full h-64 object-cover rounded-lg mb-6">
                <h1 class="text-3xl font-bold">{{ $ticket->event->name }}</h1>
                <p class="text-gray-600 mt-2">
                    Date: {{ \Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y') }} | 
                    Venue: {{ $ticket->event->location }}
                </p>
                <p class="mt-4 text-gray-700">Join us for an unforgettable night of music and entertainment. Experience the thrill of live performances from top artists in a vibrant atmosphere.</p>
            </div>

            <!-- Ticket Options -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Ticket Options</h2>
                <div class="flex justify-between items-center border-b pb-4">
                    <div>
                        <p class="font-semibold">{{ $ticket->seat_details }}</p>
                        <p class="text-gray-500">Resale Ticket</p>
                    </div>
                    <div class="text-right">
                         <p class="text-lg font-bold">KES {{ number_format($ticket->price) }}</p>
                         <button class="mt-1 bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">Purchase Now</button>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                 <h2 class="text-xl font-bold mb-4">Payment Details</h2>
                 <form action="#" class="space-y-4">
                     <div>
                        <label for="cardholder-name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                        <input type="text" id="cardholder-name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                     </div>
                     <div>
                        <label for="card-number" class="block text-sm font-medium text-gray-700">Card Number</label>
                        <input type="text" id="card-number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                     </div>
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <label for="expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                             <input type="text" id="expiry-date" placeholder="MM / YY" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                         </div>
                          <div>
                             <label for="cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                             <input type="text" id="cvv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                         </div>
                     </div>
                     <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700">Make Payment</button>
                 </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                <h3 class="text-xl font-bold mb-4">Event Organizer</h3>
                <p class="font-semibold">Music Fest Ltd.</p>
                <p class="text-sm text-gray-600">Contact: info@musicfest.co.ke</p>
                <p class="text-sm text-gray-600">Phone: +254 700 123 456</p>
            </div>
        </div>
    </div>
</x-tixswap-layout>
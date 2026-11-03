<x-tixswap-layout>
    <x-slot name="title">Your Ticket: {{ $ticket->event->name }}</x-slot>

    <!-- This script is for the "Save as Image" functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <div class="bg-white rounded-lg shadow-xl max-w-lg mx-auto my-8">
        <!-- This is the element that will be captured as an image -->
        <div id="ticket-to-save" class="p-8 bg-white">
            <div class="text-center">
                <img class="w-full h-48 object-cover rounded-lg mb-4" src="{{ asset('storage/' . $ticket->event->image_url) }}" alt="{{ $ticket->event->name }}">
                <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->event->name }}</h1>
                <p class="text-md text-gray-500 mt-2">{{ \Carbon\Carbon::parse($ticket->event->date)->format('l, F j, Y \a\t g:i A') }}</p>
                <p class="text-md text-gray-500">{{ $ticket->event->location }}</p>
            </div>

            <div class="mt-6 border-t pt-6 text-center">
                <p class="text-lg font-semibold text-gray-800">Seat: {{ $ticket->seat_details }}</p>
                <p class="text-sm text-gray-500">Presented to: {{ auth()->user()->name }}</p>
            </div>

            <div class="mt-6 pt-6 border-t flex justify-center">
                {!! $qrCode !!}
            </div>
            <p class="text-xs text-gray-400 text-center mt-2">Validation Code: {{ $ticket->validation_code }}</p>
        </div>

        <!-- Action Buttons (These are outside the captured element) -->
        <div class="p-8 border-t bg-gray-50 rounded-b-lg">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button id="save-ticket-btn" class="w-full block bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Save as Image
                </button>
                <a href="{{ route('cart.index') }}" class="w-full block text-center bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700">
                    Back to My Tickets
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('save-ticket-btn').addEventListener('click', function() {
            const ticketElement = document.getElementById('ticket-to-save');
            
            html2canvas(ticketElement, {
                // Set a white background for the captured image
                backgroundColor: '#ffffff', 
                // This helps with loading images from your server
                useCORS: true 
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'TixSwap-Ticket-{{ Str::slug($ticket->event->name) }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    </script>
</x-tixswap-layout>

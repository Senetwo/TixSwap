<x-tixswap-layout>
    <x-slot name="title">Support Center</x-slot>
    <x-slot name="navigation">
        <a href="{{ route('dashboard') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">Home</a>
        <a href="{{ route('cart.index') }}" class="text-red-100 hover:bg-red-500 hover:bg-opacity-75 rounded-md px-3 py-2 text-sm font-medium">My Tickets</a>
        <a href="{{ route('support') }}" class="bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium" aria-current="page">Support</a>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- FAQ Section -->
        <div class="bg-gray-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Support Center</h2>
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Frequently Asked Questions</h3>
            <div class="space-y-6">
                <div>
                    <h4 class="font-semibold text-gray-800">How do I list my tickets?</h4>
                    <p class="text-gray-600 mt-1">To list your tickets, navigate to the Listings section and click 'Create New Listing'. Fill in the necessary details and submit your listing.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">How do I contact a seller?</h4>
                    <p class="text-gray-600 mt-1">Contact a seller by clicking on their listing and using the 'Contact Seller' button to send a message directly.</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="bg-gray-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Contact Us</h2>
            <form action="#" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" id="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3"></textarea>
                </div>
                <div>
                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-tixswap-layout>
<x-tixswap-layout>
    <x-slot name="title">Your Profile</x-slot>
    <x-slot name="navigation">
         <!-- You can add specific navigation for the profile page if needed -->
    </x-slot>

    <div class="space-y-10">
        <!-- Profile Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
        </div>

        <!-- Ticket Management Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ticket Management</h2>
            <div class="bg-white shadow-md rounded-lg">
                <div class="p-6">
                    <h3 class="font-medium text-gray-900">Current Listings</h3>
                    <ul class="mt-4 space-y-3">
                        @forelse ($tickets as $ticket)
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                <p class="font-medium text-gray-700">{{ $ticket->event->name }}</p>
                                <a href="#" class="bg-red-600 text-white text-sm font-semibold py-1 px-4 rounded-md hover:bg-red-700">Edit</a>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 py-4">You have no active listings.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Notifications Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Notifications</h2>
            <div class="bg-white shadow-md rounded-lg">
               <div class="p-6">
                   <ul class="space-y-3">
                       <li class="text-center text-gray-500 py-4">You have no new notifications.</li>
                   </ul>
               </div>
            </div>
        </div>
    </div>
</x-tixswap-layout>
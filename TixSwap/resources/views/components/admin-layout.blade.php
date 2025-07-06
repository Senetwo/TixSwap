<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'TixSwap Admin' }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="h-full">
        <div class="flex h-full">
            <!-- Sidebar -->
            <aside class="w-64 flex-shrink-0 bg-gray-800 text-white flex flex-col">
                <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-700">
                    TixSwap Admin
                </div>
                <nav class="flex-1 px-2 py-4 space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
        <svg class="mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
        Dashboard
    </a>
    <a href="{{ route('admin.listings.index') }}" class="{{ request()->routeIs('admin.listings.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
         <svg class="mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
        Listings
    </a>
     <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
         <svg class="mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a4 4 0 00-4-4H9a4 4 0 00-4 4v1a6 6 0 006 6z" /></svg>
        Users
    </a>
</nav>

            </aside>

            <!-- Main content -->
            <div class="flex-1 flex flex-col">
                <header class="bg-white shadow-sm w-full">
                     <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex justify-end items-center">
                        <!-- User profile section -->
                         <div class="flex items-center">
                             <span class="mr-3 font-medium">{{ Auth::user()->name }}</span>
                             <form method="POST" action="{{ route('logout') }}">
                                 @csrf
                                 <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="text-gray-500 hover:text-gray-700">
                                     Log Out
                                 </a>
                             </form>
                         </div>
                     </div>
                </header>
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
                 <footer class="bg-white border-t py-4 px-6">
                     <p class="text-center text-sm text-gray-500">© 2025 TixSwap. All rights reserved.</p>
                 </footer>
            </div>
        </div>
    </body>
</html>
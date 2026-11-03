<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'TixSwap' }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-red-600 shadow-lg w-full">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 text-white font-bold text-xl">
                            <a href="{{ route('dashboard') }}">TixSwap</a>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                {{ $navigation ?? '' }}
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <a href="{{ route('profile.edit') }}" class="text-white font-medium hover:text-red-100 hover:underline cursor-pointer">{{ Auth::user()->name }}</a>
                            <div class="relative ml-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-100 hover:text-white hover:underline cursor-pointer">
                                        Log Out
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Main Content (This area will grow to fill the screen) -->
        <main class="flex-grow">
            <div class="mx-auto max-w-7xl py-8 px-4 sm:px-6 lg:px-8">
               {{ $slot }}
            </div>
        </main>
        <!-- Footer (This will be pushed to the bottom) -->
        <footer class="w-full bg-white border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-6 py-8 md:flex md:items-center md:justify-between lg:px-8">
                 <div class="text-sm text-gray-500">
                     <strong>Contact Us:</strong> Email: support@tixswap.com
                 </div>
                 <div class="mt-4 md:mt-0 flex justify-center space-x-6 md:order-2">
                    <a href="{{ route('about') }}" class="text-gray-500 hover:text-gray-600">About Us</a>
                    <a href="{{ route('privacy') }}" class="text-gray-500 hover:text-gray-600">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="text-gray-500 hover:text-gray-600">Terms & Conditions</a>
                 </div>
            </div>
        </footer>
    </body>
</html>
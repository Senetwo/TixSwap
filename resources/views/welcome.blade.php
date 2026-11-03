<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TixSwap - Your Secure Ticket Marketplace in Kenya</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="bg-gray-50">
            <header class="absolute inset-x-0 top-0 z-50">
                <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                    <div class="flex lg:flex-1">
                        <a href="#" class="-m-1.5 p-1.5">
                            <span class="text-2xl font-bold text-green-600">TixSwap</span>
                        </a>
                    </div>
                    <div class="flex lg:flex-1 lg:justify-end lg:gap-x-12">
                         @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-semibold leading-6 text-gray-900">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-900">Log in <span aria-hidden="true">&rarr;</span></a>
                            @endauth
                        @endif
                    </div>
                </nav>
            </header>

            <div class="relative isolate overflow-hidden bg-gray-900 pt-14">
                <img src="https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Concert" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-30">
                <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">The Safest Way to Resell Tickets in Kenya</h1>
                        <p class="mt-6 text-lg leading-8 text-gray-300">From Blankets & Wine to Mashemeji Derby, never miss out. Buy and sell with confidence, every ticket is verified.</p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <a href="#tickets" class="rounded-md bg-green-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">Browse Tickets</a>
                            <a href="{{ route('register') }}" class="text-sm font-semibold leading-6 text-white">Sell your ticket <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white py-24 sm:py-32">
              <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                  <h2 class="text-base font-semibold leading-7 text-green-600">Your Peace of Mind is Our Priority</h2>
                  <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Everything you need to transact safely</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                  <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                    <div class="relative pl-16">
                      <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-green-600">
                          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        Admin Verification
                      </dt>
                      <dd class="mt-2 text-base leading-7 text-gray-600">Every ticket listed is manually reviewed and verified by our team, using your proof of purchase to ensure authenticity.</dd>
                    </div>
                    <div class="relative pl-16">
                      <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-green-600">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 21z" /></svg>
                        </div>
                        Secure Payments
                      </dt>
                      <dd class="mt-2 text-base leading-7 text-gray-600">We process all transactions through trusted payment gateways. Your money is held securely until you confirm you've entered the event.</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>

            <div id="tickets" class="bg-gray-50 py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center">
                         <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Available Tickets</h2>
                    </div>
                    <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @forelse ($tickets as $ticket)
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
                                <div class="p-6">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-xl font-bold text-gray-900">{{ $ticket->event->name }}</h3>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">✓ Verified</span>
                                    </div>
                                    <p class="text-gray-600 mt-1">{{ $ticket->event->location }}</p>
                                    <p class="text-gray-500 text-sm mt-1">{{ \Carbon\Carbon::parse($ticket->event->date)->format('D, M j, Y g:i A') }}</p>
                                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                                        <p class="text-xl font-bold text-gray-800">KES {{ number_format($ticket->price) }}</p>
                                        <a href="{{ route('login') }}" class="bg-gray-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700">Buy Ticket</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 bg-white rounded-lg shadow-lg p-12 text-center">
                                <h3 class="text-xl font-semibold text-gray-900">No Tickets Available</h3>
                                <p class="mt-2 text-gray-500">There are no tickets for sale right now. Be the first to sell!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
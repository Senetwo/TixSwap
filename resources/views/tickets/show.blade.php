@php
    $event = $ticket->event;
    $date = \Illuminate\Support\Carbon::parse($event->date);
    $price = (int) ceil($ticket->price);
    $minutes = config('services.tixswap.reservation_minutes');
    $sellerName = $ticket->seller ? Str::of($ticket->seller->name)->explode(' ')->first() : 'a fan';
@endphp
<x-tixswap-layout :title="$event->name.' · '.$ticket->seat_details" :description="'KES '.number_format($price).' · '.$date->format('D j M, g:i A').' at '.$event->location.'. Verified resale ticket on TixSwap.'">
    <a href="{{ route('events.show', [$event, $event->slug()]) }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> All tickets for {{ $event->name }}
    </a>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-5 lg:gap-12">
        {{-- Event details --}}
        <div class="lg:col-span-3">
            <x-event-image :event="$event" class="aspect-[16/9] w-full" rounded="rounded-3xl" />

            <div class="mt-6">
                <x-ui.badge tone="green" class="mb-3"><x-ui.icon name="shield-check" class="h-3.5 w-3.5" /> Verified listing</x-ui.badge>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $event->name }}</h1>

                <dl class="mt-6 divide-y divide-gray-100 rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70">
                    @foreach ([
                        ['calendar', 'When', $date->format('l, F j, Y').' · '.$date->format('g:i A')],
                        ['map-pin', 'Where', $event->location],
                        ['ticket', 'Seat', $ticket->seat_details],
                        ['user', 'Sold by', $sellerName.($ticket->original_vendor && $ticket->original_vendor !== 'N/A' ? ' · originally bought from '.$ticket->original_vendor : '')],
                    ] as [$icon, $label, $value])
                        <div class="flex gap-4 px-5 py-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-600"><x-ui.icon :name="$icon" /></span>
                            <div class="min-w-0">
                                <dt class="text-sm text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium text-gray-900">{{ $value }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>

        </div>

        {{-- Checkout --}}
        <aside class="lg:col-span-2">
            <div class="rounded-3xl bg-white p-6 shadow-lift ring-1 ring-gray-200 lg:sticky lg:top-24">
                <p class="text-sm font-medium text-gray-500">Price</p>
                <p class="money mt-1 text-4xl font-bold tracking-tight text-gray-900">KES {{ number_format($price) }}</p>
                <p class="mt-1 text-sm text-gray-500">No booking fees for buyers</p>

                @if ($reserved)
                    <x-ui.alert tone="warning" class="mt-6" title="Someone is paying for this ticket">
                        If they don't finish, it'll be back on sale within {{ $minutes }} minutes.
                    </x-ui.alert>
                    <x-ui.button variant="secondary" :href="route('home')" class="mt-4 w-full">Browse other tickets</x-ui.button>
                @elseif (auth()->guest())
                    <div id="checkout" class="mt-6 space-y-3">
                        <x-ui.button size="lg" variant="mpesa" :href="route('tickets.buy', $ticket)" class="w-full">Log in to buy</x-ui.button>
                        <p class="text-center text-sm text-gray-500">New to TixSwap? <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Create a free account</a></p>
                    </div>
                @else
                    <form id="payment-form" action="{{ route('pay') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <x-ui.field label="M-Pesa phone number" name="phone_number" type="tel" inputmode="tel" autocomplete="tel"
                                    placeholder="0712 345 678" required
                                    hint="We'll send the payment prompt to this number." />
                        <x-ui.button id="pay-button" variant="mpesa" size="lg" class="w-full">
                            Pay KES {{ number_format($price) }} with M-Pesa
                        </x-ui.button>
                    </form>
                @endif

                <ul class="mt-6 space-y-3 border-t border-gray-100 pt-6 text-sm text-gray-600">
                    <li class="flex gap-3"><x-ui.icon name="lock" class="h-5 w-5 text-mpesa-600" /> <span>You'll enter your PIN <strong class="font-semibold text-gray-900">on your phone</strong>. TixSwap never asks for it.</span></li>
                    <li class="flex gap-3"><x-ui.icon name="clock" class="h-5 w-5 text-mpesa-600" /> <span>The ticket is held for you for {{ $minutes }} minutes while you pay.</span></li>
                    <li class="flex gap-3"><x-ui.icon name="shield-check" class="h-5 w-5 text-mpesa-600" /> <span>Money held in escrow until after the event. Full refund if the ticket doesn't work.</span></li>
                </ul>
            </div>
        </aside>
    </div>

    <section class="mt-12 lg:max-w-[60%]">
            <h2 class="text-lg font-semibold text-gray-900">How buying works</h2>
            <ol class="mt-4 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Pay with M-Pesa', 'Enter your number and approve the prompt on your phone.'],
                    ['Get your ticket', 'Your QR ticket appears instantly in My tickets.'],
                    ['You\'re protected', 'The seller is paid after the event. Problem? We refund you.'],
                ] as $i => [$heading, $text])
                    <li class="rounded-2xl bg-white p-5 shadow-card ring-1 ring-gray-200/70">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-sm font-bold text-brand-700">{{ $i + 1 }}</span>
                        <p class="mt-3 font-semibold text-gray-900">{{ $heading }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

    @push('scripts')
        <script>
            // Prevent double submissions: each click would send another M-Pesa prompt.
            document.getElementById('payment-form')?.addEventListener('submit', function () {
                const button = document.getElementById('pay-button');
                button.disabled = true;
                button.textContent = 'Sending M-Pesa prompt…';
            });
        </script>
    @endpush
</x-tixswap-layout>

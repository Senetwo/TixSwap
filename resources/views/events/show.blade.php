@php
    $cheapest = $tickets->min('price');
    $description = $event->date->format('D j M, g:i A').' at '.$event->location.'. '
        .($tickets->count() ? $tickets->count().' verified '.Str::plural('ticket', $tickets->count()).' from KES '.number_format((int) ceil($cheapest)).' on TixSwap.' : 'Resale tickets on TixSwap.');
    $hasImage = $event->image_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->image_url);
@endphp
<x-tixswap-layout :title="$event->name" :description="$description" :image="$hasImage ? asset('storage/'.$event->image_url) : null">
    @push('head')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->name,
            'startDate' => $event->date->toIso8601String(),
            'location' => ['@type' => 'Place', 'name' => $event->location, 'address' => $event->location],
            'offers' => $tickets->isEmpty() ? null : [
                '@type' => 'AggregateOffer', 'priceCurrency' => 'KES',
                'lowPrice' => (int) ceil($cheapest), 'highPrice' => (int) ceil($tickets->max('price')),
                'offerCount' => $tickets->count(), 'url' => url()->current(),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush

    <a href="{{ route('home') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> All events
    </a>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-5 lg:gap-12">
        <div class="lg:col-span-2">
            <x-event-image :event="$event" class="aspect-[16/9] w-full" rounded="rounded-3xl" />
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">{{ $event->name }}</h1>
            <ul class="mt-4 space-y-2 text-gray-600">
                <li class="flex items-center gap-2"><x-ui.icon name="calendar" class="text-gray-400" /> {{ $event->date->format('l, F j, Y · g:i A') }}</li>
                <li class="flex items-center gap-2"><x-ui.icon name="map-pin" class="text-gray-400" /> {{ $event->location }}</li>
            </ul>
            @if ($event->isPast())
                <x-ui.alert tone="info" class="mt-6">This event has already happened.</x-ui.alert>
            @endif
        </div>

        <div class="lg:col-span-3">
            <div class="mb-4 flex items-baseline justify-between">
                <h2 class="text-lg font-semibold text-gray-900">{{ $tickets->count() }} {{ Str::plural('ticket', $tickets->count()) }} available</h2>
                @if ($tickets->count() > 1) <p class="text-sm text-gray-500">Cheapest first</p> @endif
            </div>

            @if ($tickets->isEmpty() || $event->isPast())
                <x-ui.empty-state icon="ticket" title="No tickets available" description="Every ticket for this event has been sold. New ones may be listed, so check back.">
                    <x-ui.button variant="secondary" :href="route('home')">Browse other events</x-ui.button>
                </x-ui.empty-state>
            @else
                <ul class="space-y-3">
                    @foreach ($tickets as $ticket)
                        <li>
                            <a href="{{ route('tickets.show', $ticket) }}" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-gray-200/70 transition hover:shadow-lift hover:ring-gray-300">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-ui.icon name="ticket" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex flex-wrap items-center gap-2 font-semibold text-gray-900">{{ $ticket->seat_details }} @if ($ticket->seller?->isTrustedSeller()) <x-ui.badge tone="green">Trusted seller</x-ui.badge> @endif</p>
                                    <p class="mt-0.5 flex items-center gap-1.5 truncate text-sm text-gray-500">
                                        <x-ui.icon name="shield-check" class="h-4 w-4 text-green-600" /> Verified · sold by {{ Str::of($ticket->seller?->name ?? 'a fan')->explode(' ')->first() }}
                                        @if ($ticket->face_value && $ticket->markupPercent() <= 0) · <span class="font-medium text-green-700">{{ $ticket->markupPercent() === 0 ? 'At face value' : 'Below face value' }}</span> @endif
                                    </p>
                                </div>
                                <p class="money text-lg font-bold text-gray-900">KES {{ number_format((int) ceil($ticket->price)) }}</p>
                                <x-ui.icon name="chevron-right" class="h-5 w-5 text-gray-400 transition group-hover:translate-x-0.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-tixswap-layout>

@php($searching = request()->filled('search'))
<x-tixswap-layout :title="$searching ? 'Tickets for “'.request('search').'”' : null">
    {{-- Search-led hero --}}
    <section class="relative mb-10 overflow-hidden rounded-3xl bg-gray-950 px-6 py-10 sm:px-10 sm:py-14">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-brand-600/50 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-orange-500/25 blur-3xl" aria-hidden="true"></div>
        <div class="relative max-w-2xl">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-5xl">Find tickets to what's on</h1>
            <p class="mt-3 text-gray-300 sm:text-lg">Verified tickets from real fans. Pay with M-Pesa; your money is protected until after the event.</p>

            <form action="{{ route('home') }}" method="GET" role="search" class="mt-6 flex flex-col gap-2 sm:flex-row">
                <label for="search" class="sr-only">Search events</label>
                <div class="relative flex-1">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Search by event or city"
                           class="block w-full rounded-xl border-0 py-3.5 pl-12 pr-4 text-gray-900 shadow-sm ring-1 ring-white/10 placeholder:text-gray-400 focus:ring-2 focus:ring-white/60">
                </div>
                <x-ui.button size="lg" class="rounded-xl">Search</x-ui.button>
            </form>

            <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-300">
                <li class="flex items-center gap-1.5"><x-ui.icon name="shield-check" class="h-4 w-4 text-green-400" /> Every listing checked</li>
                <li class="flex items-center gap-1.5"><x-ui.icon name="phone" class="h-4 w-4 text-green-400" /> M-Pesa checkout</li>
                <li class="flex items-center gap-1.5"><x-ui.icon name="lock" class="h-4 w-4 text-green-400" /> Refund if it doesn't work</li>
            </ul>
        </div>
    </section>

    <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
        <h2 class="text-xl font-semibold text-gray-900">{{ $searching ? 'Results for “'.request('search').'”' : 'Upcoming events' }}</h2>
        <p class="text-sm text-gray-500">
            {{ $events->total() }} {{ Str::plural('event', $events->total()) }}
            @if ($searching) · <a href="{{ route('home') }}" class="font-medium text-brand-600 hover:text-brand-700">Clear search</a> @endif
        </p>
    </div>

    @if ($events->isEmpty())
        <x-ui.empty-state icon="ticket"
            :title="$searching ? 'No events match your search' : 'No tickets on sale right now'"
            :description="$searching ? 'Try a different event name or city.' : 'New tickets are listed every day. Check back soon.'">
            @if ($searching) <x-ui.button variant="secondary" :href="route('home')">See all events</x-ui.button> @endif
        </x-ui.empty-state>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($events as $event)
                <x-event-card :event="$event" />
            @endforeach
        </div>
        <div class="mt-8">{{ $events->links() }}</div>
    @endif

    {{-- Sell CTA for visitors and buyers --}}
    @unless (auth()->user()?->isSeller() || auth()->user()?->isAdmin())
        <section class="mt-16 flex flex-col items-start justify-between gap-6 rounded-3xl bg-white p-8 shadow-card ring-1 ring-gray-200/70 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900">Can't make it to an event?</h2>
                <p class="mt-1 text-gray-600">List your ticket in two minutes. We find the buyer, and you're paid to M-Pesa after the event.</p>
            </div>
            <x-ui.button size="lg" variant="dark" :href="auth()->check() ? route('support') : route('register')">
                {{ auth()->check() ? 'How to sell' : 'Start selling' }}
            </x-ui.button>
        </section>
    @endunless
</x-tixswap-layout>

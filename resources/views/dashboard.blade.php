<x-tixswap-layout title="Browse tickets">
    {{-- Search-led hero --}}
    <section class="relative mb-10 overflow-hidden rounded-3xl bg-gray-950 px-6 py-10 sm:px-10 sm:py-14">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-brand-600/50 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-orange-500/25 blur-3xl" aria-hidden="true"></div>
        <div class="relative max-w-2xl">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Find tickets to what's on</h1>
            <p class="mt-2 text-gray-300">Verified tickets from real fans. Pay with M-Pesa; your money is protected until after the event.</p>

            <form action="{{ route('dashboard') }}" method="GET" role="search" class="mt-6 flex flex-col gap-2 sm:flex-row">
                <label for="search" class="sr-only">Search events</label>
                <div class="relative flex-1">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input id="search" type="search" name="search" value="{{ request('search') }}"
                           placeholder="Search by event or city"
                           class="block w-full rounded-xl border-0 py-3.5 pl-12 pr-4 text-gray-900 shadow-sm ring-1 ring-white/10 placeholder:text-gray-400 focus:ring-2 focus:ring-white/60">
                </div>
                <x-ui.button size="lg" class="rounded-xl">Search</x-ui.button>
            </form>
        </div>
    </section>

    <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
        <h2 class="text-xl font-semibold text-gray-900">
            @if (request()->filled('search'))
                Results for “{{ request('search') }}”
            @else
                Available tickets
            @endif
        </h2>
        <p class="text-sm text-gray-500">
            {{ $tickets->count() }} {{ Str::plural('ticket', $tickets->count()) }}
            @if (request()->filled('search'))
                · <a href="{{ route('dashboard') }}" class="font-medium text-brand-600 hover:text-brand-700">Clear search</a>
            @endif
        </p>
    </div>

    @if ($tickets->isEmpty())
        <x-ui.empty-state icon="ticket"
            :title="request()->filled('search') ? 'No tickets match your search' : 'No tickets on sale right now'"
            :description="request()->filled('search') ? 'Try a different event name or city.' : 'New tickets are listed every day. Check back soon.'">
            @if (request()->filled('search'))
                <x-ui.button variant="secondary" :href="route('dashboard')">See all tickets</x-ui.button>
            @endif
        </x-ui.empty-state>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($tickets as $ticket)
                <x-ticket-card :ticket="$ticket" />
            @endforeach
        </div>
    @endif
</x-tixswap-layout>

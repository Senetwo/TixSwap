<x-tixswap-layout title="My tickets" width="max-w-3xl">
    <x-ui.page-header title="My tickets" description="Show the QR code at the entrance. Tickets you open are saved on this phone, so they work without signal." />

    <div data-install-app hidden class="mb-6">
        <div class="flex items-center gap-4 rounded-2xl bg-gray-900 p-4 text-white">
            <img src="{{ asset('icon-192.png') }}" alt="" class="h-11 w-11 rounded-xl">
            <div class="min-w-0 flex-1">
                <p class="font-semibold">Install the TixSwap app</p>
                <p class="text-sm text-gray-300">One tap to your tickets, even offline.</p>
            </div>
            <button type="button" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900">Install</button>
        </div>
    </div>

    @if ($tickets->isEmpty())
        <x-ui.empty-state icon="ticket" title="No tickets yet" description="When you buy a ticket it will appear here, ready to show at the gate.">
            <x-ui.button :href="route('home')">Browse tickets</x-ui.button>
        </x-ui.empty-state>
    @else
        @if ($upcoming->isNotEmpty())
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Upcoming</h2>
            <ul class="space-y-3">
                @foreach ($upcoming as $ticket)
                    @include('cart._row')
                @endforeach
            </ul>
        @endif

        @if ($past->isNotEmpty())
            <h2 class="mb-3 mt-10 text-sm font-semibold uppercase tracking-wide text-gray-500">Past</h2>
            <ul class="space-y-3 opacity-80">
                @foreach ($past as $ticket)
                    @include('cart._row')
                @endforeach
            </ul>
        @endif
    @endif
</x-tixswap-layout>

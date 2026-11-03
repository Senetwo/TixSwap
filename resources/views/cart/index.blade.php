<x-tixswap-layout title="My tickets" width="max-w-3xl">
    <x-ui.page-header title="My tickets" description="Show the QR code at the entrance. Tickets work offline once saved as an image." />

    @if ($tickets->isEmpty())
        <x-ui.empty-state icon="ticket" title="No tickets yet" description="When you buy a ticket it will appear here, ready to show at the gate.">
            <x-ui.button :href="route('dashboard')">Browse tickets</x-ui.button>
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

@php($fee = rtrim(rtrim(number_format(config('services.tixswap.commission_percent'), 1), '0'), '.'))
<x-tixswap-layout title="My listings">
    <x-ui.page-header title="My listings" description="Track your tickets, sales and payouts.">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('seller.tickets.create')">Sell a ticket</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($needsPayoutPhone)
        <x-ui.alert tone="warning" class="mb-6" title="Add your payout number">
            We send your earnings to M-Pesa after each event.
            <a href="{{ route('profile.edit') }}" class="font-semibold underline">Add your M-Pesa number</a>
        </x-ui.alert>
    @endif

    <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat label="Held in escrow" :value="'KES '.number_format($earnings['in_escrow'])" icon="lock" tone="blue" hint="Released 24h after each event">
            @if ($earnings['disputed'] > 0)
                <p class="mt-2 text-sm font-medium text-amber-700">KES {{ number_format($earnings['disputed']) }} on hold: buyer reported a problem</p>
            @endif
        </x-ui.stat>
        <x-ui.stat label="Ready for payout" :value="'KES '.number_format($earnings['awaiting_payout'])" icon="banknotes" tone="amber" hint="Being sent to your M-Pesa" />
        <x-ui.stat label="Paid out" :value="'KES '.number_format($earnings['paid_out'])" icon="check-circle" tone="green" :hint="'After the '.$fee.'% TixSwap fee'" />
    </div>

    @if (! $hasListings)
        <x-ui.empty-state icon="ticket" title="List your first ticket" description="Can't make it to an event? List your ticket in two minutes. We check it, buyers pay with M-Pesa, and you're paid after the event.">
            <x-ui.button icon="plus" :href="route('seller.tickets.create')">Sell a ticket</x-ui.button>
        </x-ui.empty-state>
    @else
        {{-- Status tabs --}}
        <div class="mb-4 overflow-x-auto">
            <nav class="flex min-w-max gap-1 rounded-xl bg-gray-100 p-1" aria-label="Filter listings">
                @foreach ($tabs as $key => $t)
                    <a href="{{ route('seller.dashboard', $key === 'all' ? [] : ['tab' => $key]) }}" @class([
                        'flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition',
                        'bg-white text-gray-900 shadow-sm' => $tab === $key,
                        'text-gray-600 hover:text-gray-900' => $tab !== $key,
                    ]) @if ($tab === $key) aria-current="page" @endif>
                        {{ $t['label'] }}
                        <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-gray-100 text-gray-700' => $tab === $key, 'bg-gray-200/70 text-gray-500' => $tab !== $key])>{{ $t['count'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($tickets->isEmpty())
            <x-ui.empty-state icon="inbox" title="Nothing here" description="No listings with this status." />
        @else
            <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70">
                @foreach ($tickets as $ticket)
                    <li class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <x-event-image :event="$ticket->event" class="h-16 w-16" rounded="rounded-xl" />
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate font-semibold text-gray-900">{{ $ticket->event->name }}</p>
                                    <x-ui.status :status="$ticket->status" />
                                </div>
                                <p class="mt-0.5 truncate text-sm text-gray-500">
                                    {{ \Illuminate\Support\Carbon::parse($ticket->event->date)->format('D, M j') }} · {{ $ticket->seat_details }}
                                </p>
                                @if (in_array($ticket->id, $disputedTicketIds))
                                    <p class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-amber-700"><x-ui.icon name="warning" class="h-4 w-4" /> Buyer reported a problem. Payment on hold while we review.</p>
                                @elseif ($ticket->status === 'rejected' && $ticket->admin_notes)
                                    <p class="mt-1.5 text-sm text-red-700"><span class="font-medium">Why:</span> {{ $ticket->admin_notes }}</p>
                                @elseif ($ticket->status === 'pending')
                                    <p class="mt-1.5 text-sm text-gray-500">We usually review listings within a few hours.</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <p class="money text-right font-semibold text-gray-900">KES {{ number_format($ticket->price) }}</p>
                            @can('update', $ticket)
                                <x-ui.button variant="secondary" size="sm" icon="pencil" :href="route('seller.tickets.edit', $ticket)">Edit</x-ui.button>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6">{{ $tickets->links() }}</div>
        @endif
    @endif
</x-tixswap-layout>

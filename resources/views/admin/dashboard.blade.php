<x-admin-layout title="Overview">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Sales" :value="'KES '.number_format($kpis['sales'])" icon="chart" tone="blue" hint="Paid orders, excluding refunds" />
        <x-ui.stat label="TixSwap revenue" :value="'KES '.number_format($kpis['revenue'])" icon="banknotes" tone="green" hint="Commission earned" />
        <x-ui.stat label="Held in escrow" :value="'KES '.number_format($kpis['escrow'])" icon="lock" tone="amber" hint="Waiting for events to finish" />
        <x-ui.stat label="Tickets sold" :value="number_format($kpis['sold'])" icon="ticket" tone="brand" :hint="number_format($kpis['live']).' live · '.number_format($kpis['users']).' users'" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card title="Needs attention" padding="p-2" class="lg:col-span-1">
            @php($allClear = collect($attention)->sum(fn ($a) => $a[1]) === 0)
            @if ($allClear)
                <div class="flex flex-col items-center px-4 py-8 text-center">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-green-50 text-green-600"><x-ui.icon name="check-circle" class="h-6 w-6" /></span>
                    <p class="mt-3 font-semibold text-gray-900">All clear</p>
                    <p class="text-sm text-gray-500">Nothing is waiting for you.</p>
                </div>
            @else
                <ul>
                    @foreach ($attention as [$label, $count, $route, $icon])
                        <li>
                            <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-gray-50">
                                <span @class(['flex h-9 w-9 items-center justify-center rounded-lg', 'bg-brand-50 text-brand-600' => $count, 'bg-gray-100 text-gray-400' => ! $count])>
                                    <x-ui.icon :name="$icon" />
                                </span>
                                <span @class(['flex-1 text-sm font-medium', 'text-gray-900' => $count, 'text-gray-500' => ! $count])>{{ $label }}</span>
                                <span @class(['money text-sm font-semibold', 'text-gray-900' => $count, 'text-gray-400' => ! $count])>{{ $count }}</span>
                                <x-ui.icon name="chevron-right" class="h-4 w-4 text-gray-300" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card title="Latest listings" padding="p-0" class="lg:col-span-2">
            <x-slot:actions>
                <a href="{{ route('admin.listings.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
            </x-slot:actions>
            @if ($recent->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-gray-500">No listings yet.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($recent as $ticket)
                        <li class="flex items-center gap-4 px-6 py-3">
                            <x-event-image :event="$ticket->event" class="h-10 w-10" rounded="rounded-lg" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $ticket->event->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $ticket->seller?->name }} · {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                            <p class="money hidden text-sm text-gray-700 sm:block">KES {{ number_format($ticket->price) }}</p>
                            <x-ui.status :status="$ticket->status" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</x-admin-layout>

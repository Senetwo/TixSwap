@php
    $event = $ticket->event;
    $date = \Illuminate\Support\Carbon::parse($event->date);
    $dispute = $ticket->orders->first()?->dispute;
@endphp
<li>
    <a href="{{ route('tickets.purchased.show', $ticket) }}"
       class="group flex items-center gap-4 rounded-2xl bg-white p-3 pr-4 shadow-card ring-1 ring-gray-200/70 transition hover:shadow-lift hover:ring-gray-300 sm:p-4">
        <div class="relative shrink-0">
            <x-event-image :event="$event" class="h-20 w-20 sm:h-24 sm:w-24" rounded="rounded-xl" />
            <x-date-chip :date="$event->date" class="absolute -bottom-2 -right-2 scale-90" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-gray-900 group-hover:text-brand-700">{{ $event->name }}</p>
            <p class="mt-0.5 truncate text-sm text-gray-500">{{ $date->format('D, M j · g:i A') }} · {{ $event->location }}</p>
            <p class="mt-0.5 truncate text-sm text-gray-500">{{ $ticket->seat_details }}</p>
            <div class="mt-2">
                @if ($dispute)
                    @php([$label, $tone] = ['open' => ['Problem reported', 'amber'], 'refunded' => ['Refund approved', 'green'], 'released' => ['Report closed', 'gray']][$dispute->status] ?? ['Reported', 'gray'])
                    <x-ui.badge :tone="$tone" dot>{{ $label }}</x-ui.badge>
                @elseif ($ticket->checked_in_at)
                    <x-ui.badge dot>Used · {{ $ticket->checked_in_at->format('M j, g:i A') }}</x-ui.badge>
                @elseif ($date->isFuture())
                    <x-ui.badge tone="green" dot>Valid ticket</x-ui.badge>
                @else
                    <x-ui.badge>Event ended</x-ui.badge>
                @endif
            </div>
        </div>
        <x-ui.icon name="chevron-right" class="h-5 w-5 text-gray-400 transition group-hover:translate-x-0.5" />
    </a>
</li>

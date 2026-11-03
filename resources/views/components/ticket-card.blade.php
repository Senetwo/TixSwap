@props(['ticket'])
@php($event = $ticket->event)
<a href="{{ route('tickets.show', $ticket) }}"
   {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70 transition duration-200 hover:-translate-y-0.5 hover:shadow-lift focus-visible:ring-2']) }}>
    <div class="relative aspect-[16/9]">
        <x-event-image :event="$event" class="h-full w-full" />
        <x-date-chip :date="$event->date" class="absolute left-3 top-3" />
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="line-clamp-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ $event->name }}</h3>
        <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
            <x-ui.icon name="map-pin" class="h-4 w-4" />
            <span class="truncate">{{ $event->location }}</span>
            <span aria-hidden="true">·</span>
            <span class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($event->date)->format('D, g:i A') }}</span>
        </p>
        <div class="mt-4 flex items-end justify-between gap-3 border-t border-gray-100 pt-3">
            <div class="min-w-0">
                <p class="truncate text-xs text-gray-500">{{ $ticket->seat_details }}</p>
                <p class="money text-lg font-bold text-gray-900">KES {{ number_format((int) ceil($ticket->price)) }}</p>
            </div>
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600">
                View <x-ui.icon name="chevron-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
            </span>
        </div>
    </div>
</a>

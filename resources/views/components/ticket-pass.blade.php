@props(['ticket', 'qrCode'])
@php($event = $ticket->event)
{{-- Wallet-style ticket. #ticket-pass is what "Save as image" captures. --}}
<div id="ticket-pass" {{ $attributes->merge(['class' => 'mx-auto w-full max-w-sm overflow-hidden rounded-3xl bg-white shadow-lift ring-1 ring-gray-200']) }}>
    <div class="relative h-36">
        <x-event-image :event="$event" class="h-full w-full" />
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/85 via-gray-950/30 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-5 text-white">
            <p class="text-xs font-semibold uppercase tracking-wider text-white/70">Admit one</p>
            <h2 class="text-xl font-bold leading-tight">{{ $event->name }}</h2>
        </div>
    </div>

    <dl class="grid grid-cols-2 gap-x-4 gap-y-4 p-5 text-sm">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Date</dt>
            <dd class="mt-0.5 font-semibold text-gray-900">{{ \Illuminate\Support\Carbon::parse($event->date)->format('D, M j, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Time</dt>
            <dd class="mt-0.5 font-semibold text-gray-900">{{ \Illuminate\Support\Carbon::parse($event->date)->format('g:i A') }}</dd>
        </div>
        <div class="col-span-2">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Venue</dt>
            <dd class="mt-0.5 font-semibold text-gray-900">{{ $event->location }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Seat</dt>
            <dd class="mt-0.5 font-semibold text-gray-900">{{ $ticket->seat_details }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Ticket holder</dt>
            <dd class="mt-0.5 truncate font-semibold text-gray-900">{{ $ticket->buyer?->name ?? auth()->user()?->name }}</dd>
        </div>
    </dl>

    {{-- Perforation --}}
    <div class="relative flex items-center" aria-hidden="true">
        <span class="absolute -left-3 h-6 w-6 rounded-full bg-gray-50 ring-1 ring-gray-200"></span>
        <span class="mx-5 w-full border-t-2 border-dashed border-gray-200"></span>
        <span class="absolute -right-3 h-6 w-6 rounded-full bg-gray-50 ring-1 ring-gray-200"></span>
    </div>

    <div class="flex flex-col items-center p-6">
        <div class="rounded-xl bg-white p-2 ring-1 ring-gray-200 [&>svg]:h-48 [&>svg]:w-48">{!! $qrCode !!}</div>
        <p class="mt-3 text-center text-xs text-gray-500">Show this code at the entrance</p>
        <p class="mt-1 break-all text-center font-mono text-[11px] text-gray-400">{{ $ticket->validation_code }}</p>
    </div>
</div>

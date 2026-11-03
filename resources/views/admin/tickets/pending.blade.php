<x-admin-layout title="Review queue">
    <p class="mb-6 text-sm text-gray-600">Check each proof of purchase matches the listing. Warnings flag tickets that look like they've been listed before.</p>

    <div class="space-y-4">
        @forelse ($tickets as $ticket)
            @php($dups = $duplicates[$ticket->id] ?? collect())
            <x-ui.card padding="p-0" @class(['ring-red-300' => $dups->contains('other_seller', true)])>
                <div class="flex flex-col gap-5 p-5 md:flex-row">
                    <x-event-image :event="$ticket->event" class="h-28 w-full md:h-24 md:w-36" rounded="rounded-xl" />

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">{{ $ticket->event->name }}</h2>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{ \Illuminate\Support\Carbon::parse($ticket->event->date)->format('D, M j, Y · g:i A') }} · {{ $ticket->event->location }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="money text-lg font-bold text-gray-900">KES {{ number_format($ticket->price) }}</p>
                                @if ($ticket->face_value)
                                    <p class="text-xs text-gray-500">Face KES {{ number_format($ticket->face_value) }} · {{ ($ticket->markupPercent() > 0 ? '+' : '').$ticket->markupPercent() }}%</p>
                                @endif
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-4">
                            <div><dt class="text-gray-500">Seat</dt><dd class="font-medium text-gray-900">{{ $ticket->seat_details }}</dd></div>
                            <div><dt class="text-gray-500">Bought from</dt><dd class="font-medium text-gray-900">{{ $ticket->original_vendor ?: 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Ticket no.</dt><dd class="font-mono text-gray-900">{!! $ticket->ticket_reference ? e($ticket->ticket_reference) : '<span class="font-sans text-amber-700">None given</span>' !!}</dd></div>
                            <div>
                                <dt class="text-gray-500">Seller</dt>
                                <dd class="truncate font-medium text-gray-900" title="{{ $ticket->seller->email }}">{{ $ticket->seller->name }}</dd>
                                @php($st = $ticket->seller->sellerStats())
                                <dd class="text-xs {{ $st['refunded'] ? 'font-medium text-red-700' : 'text-gray-500' }}">
                                    {{ $st['completed'] }} completed {{ Str::plural('sale', $st['completed']) }}@if ($st['refunded']) · {{ $st['refunded'] }} refunded @endif @if ($st['ratings']) · {{ $st['percent'] }}% 👍 @endif
                                </dd>
                            </div>
                        </dl>

                        @foreach ($dups as $dup)
                            <x-ui.alert :tone="$dup['other_seller'] ? 'error' : 'warning'" class="mt-4">
                                Same {{ $dup['match'] === 'proof' ? 'proof file' : 'ticket number' }} as listing #{{ $dup['ticket']->id }}
                                ({{ $dup['other_seller'] ? 'different seller: '.$dup['ticket']->seller?->name : 'same seller' }}, {{ $dup['ticket']->status }})
                            </x-ui.alert>
                        @endforeach
                    </div>
                </div>

                <div x-data="{ rejecting: false }" class="border-t border-gray-100 bg-gray-50/60 px-5 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('tickets.proof', $ticket) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700">
                            <x-ui.icon name="document" class="h-4 w-4" /> View proof of purchase <x-ui.icon name="external" class="h-3.5 w-3.5" />
                        </a>
                        <div class="flex gap-2" x-show="!rejecting">
                            <x-ui.button type="button" variant="secondary" size="sm" @click="rejecting = true">Reject</x-ui.button>
                            <form action="{{ route('admin.tickets.approve', $ticket) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <x-ui.button size="sm" icon="check" class="!bg-green-600 hover:!bg-green-700">Approve</x-ui.button>
                            </form>
                        </div>
                    </div>
                    <form x-cloak x-show="rejecting" action="{{ route('admin.tickets.reject', $ticket) }}" method="POST" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')
                        <label for="reason-{{ $ticket->id }}" class="block text-sm font-medium text-gray-800">Reason (shown to the seller)</label>
                        <textarea id="reason-{{ $ticket->id }}" name="rejection_reason" rows="2" required maxlength="1000" class="form-control text-sm"
                                  placeholder="e.g. The proof of purchase is unreadable. Please upload a clearer photo."></textarea>
                        <div class="flex justify-end gap-2">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="rejecting = false">Cancel</x-ui.button>
                            <x-ui.button variant="danger" size="sm">Reject listing</x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state icon="check-circle" title="Queue is empty" description="Every listing has been reviewed. New ones will appear here." />
        @endforelse
    </div>
</x-admin-layout>

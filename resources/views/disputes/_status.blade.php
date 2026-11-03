{{-- On a purchased ticket: the report link, or the dispute's status. Expects $order (nullable). --}}
@if ($order)
    @if ($order->dispute)
        @php($d = $order->dispute)
        @if ($d->isOpen())
            <x-ui.alert tone="warning" title="Problem reported">Our team is reviewing it. The seller's payment is on hold until we decide.</x-ui.alert>
        @elseif ($d->status === \App\Models\Dispute::STATUS_REFUNDED)
            <x-ui.alert tone="success" title="Resolved in your favour">
                You'll be refunded to your M-Pesa number.
                @if ($d->resolution_note) <span class="mt-1 block text-green-800/80">{{ $d->resolution_note }}</span> @endif
            </x-ui.alert>
        @else
            <x-ui.alert tone="info" title="Report closed">
                After review, the payment was released to the seller.
                @if ($d->resolution_note) <span class="mt-1 block text-blue-800/80">{{ $d->resolution_note }}</span> @endif
            </x-ui.alert>
        @endif
    @elseif ($order->canBeDisputed())
        <div class="rounded-2xl bg-white p-4 text-center text-sm text-gray-600 ring-1 ring-gray-200">
            Problem with this ticket?
            <a href="{{ route('disputes.create', $order) }}" class="font-semibold text-brand-600 hover:text-brand-700">Report it</a>
            before {{ $order->escrow_release_at->format('M j, g:i A') }} and we'll hold the seller's payment.
        </div>
    @endif
@endif

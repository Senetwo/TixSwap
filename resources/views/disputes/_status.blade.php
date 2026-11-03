{{-- Shown on a purchased ticket: report link or the dispute's status. Expects $order (nullable). --}}
@if ($order)
    @if ($order->dispute)
        @php($d = $order->dispute)
        <div @class([
            'rounded-lg border p-4 text-sm',
            'bg-yellow-50 border-yellow-200 text-yellow-900' => $d->isOpen(),
            'bg-green-50 border-green-200 text-green-900' => $d->status === \App\Models\Dispute::STATUS_REFUNDED,
            'bg-gray-50 border-gray-200 text-gray-800' => $d->status === \App\Models\Dispute::STATUS_RELEASED,
        ])>
            @if ($d->isOpen())
                <strong>Problem reported.</strong> Our team is reviewing it and the seller's payment is on hold.
            @elseif ($d->status === \App\Models\Dispute::STATUS_REFUNDED)
                <strong>Resolved in your favour.</strong> You'll be refunded to your M-Pesa number.
                @if ($d->resolution_note) <br><span class="text-gray-700">{{ $d->resolution_note }}</span> @endif
            @else
                <strong>Report closed.</strong> After review, the payment was released to the seller.
                @if ($d->resolution_note) <br><span class="text-gray-700">{{ $d->resolution_note }}</span> @endif
            @endif
        </div>
    @elseif ($order->canBeDisputed())
        <p class="text-sm text-gray-600 text-center">
            Problem with this ticket?
            <a href="{{ route('disputes.create', $order) }}" class="font-semibold text-red-600 hover:text-red-700 underline">Report it</a>
            before {{ $order->escrow_release_at->format('M j, g:i A') }} and we'll hold the seller's payment.
        </p>
    @endif
@endif

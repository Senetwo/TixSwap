<x-admin-layout>
    <x-slot name="title">Disputes</x-slot>

    <h1 class="text-2xl font-bold mb-2">Disputes</h1>
    <p class="text-sm text-gray-500 mb-6">The seller's money stays in escrow until you decide. Refunds then appear on the Payouts page to be sent and recorded.</p>

    <h2 class="text-lg font-semibold mb-3">Open ({{ $open->count() }})</h2>
    <div class="space-y-6 mb-10">
        @forelse ($open as $dispute)
            @php($order = $dispute->order)
            <div class="bg-white shadow-md rounded-lg p-6 border-l-4 border-yellow-400">
                <div class="flex flex-wrap justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order #{{ $order->id }} · opened {{ $dispute->created_at->diffForHumans() }}</p>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $dispute->reasonLabel() }}</h3>
                        <p class="text-sm text-gray-600">{{ $order->ticket?->event?->name }} · Seat {{ $order->ticket?->seat_details }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold">KES {{ number_format($order->amount) }}</p>
                        <p class="text-xs text-gray-500">M-Pesa receipt: <span class="font-mono">{{ $order->payment?->transaction_id ?? '—' }}</span></p>
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Buyer</dt>
                        <dd>{{ $dispute->buyer?->name }} · {{ $dispute->buyer?->email }} · <span class="font-mono">{{ $order->phone_number }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Seller</dt>
                        <dd>{{ $order->ticket?->seller?->name }} · {{ $order->ticket?->seller?->email }}</dd>
                    </div>
                </dl>

                <blockquote class="mt-4 rounded-md bg-gray-50 p-4 text-gray-800 whitespace-pre-line">{{ $dispute->details }}</blockquote>

                <div class="mt-3 flex flex-wrap gap-4 text-sm">
                    @if ($dispute->evidence_path)
                        <a href="{{ route('admin.disputes.evidence', $dispute) }}" target="_blank" class="text-blue-600 hover:underline">View buyer's evidence</a>
                    @endif
                    @if ($order->ticket?->purchase_proof)
                        <a href="{{ route('tickets.proof', $order->ticket) }}" target="_blank" class="text-blue-600 hover:underline">View seller's proof of purchase</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="mt-6 border-t pt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <label for="note-{{ $dispute->id }}" class="block text-sm font-medium text-gray-700">Decision note (shown to the buyer)</label>
                    <textarea id="note-{{ $dispute->id }}" name="note" rows="2" required maxlength="2000" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                    <div class="flex flex-wrap justify-end gap-3">
                        <button name="decision" value="release" class="bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-gray-800"
                                onclick="return confirm('Release KES {{ number_format($order->seller_amount) }} to the seller?')">
                            Release to seller
                        </button>
                        <button name="decision" value="refund" class="bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-red-700"
                                onclick="return confirm('Refund KES {{ number_format($order->amount) }} to the buyer?')">
                            Refund buyer
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white shadow-md rounded-lg p-8 text-center text-gray-500">No open disputes.</div>
        @endforelse
    </div>

    <h2 class="text-lg font-semibold mb-3">Recently resolved</h2>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Decision</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($resolved as $dispute)
                    <tr>
                        <td class="px-6 py-4 text-sm">#{{ $dispute->order_id }}</td>
                        <td class="px-6 py-4 text-sm">{{ $dispute->reasonLabel() }}</td>
                        <td class="px-6 py-4 text-sm">{{ $dispute->status === 'refunded' ? 'Refund buyer' : 'Release to seller' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $dispute->resolver?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $dispute->resolved_at?->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nothing resolved yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>

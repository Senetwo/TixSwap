<x-admin-layout title="Disputes">
    <p class="mb-6 text-sm text-gray-600">The seller's money stays in escrow until you decide. Refunds then move to Payouts &amp; refunds, to be sent and recorded.</p>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Open · {{ $open->count() }}</h2>
    <div class="mb-12 space-y-4">
        @forelse ($open as $dispute)
            @php($order = $dispute->order)
            <x-ui.card padding="p-0">
                <div class="p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.status type="dispute" :status="$dispute->status" />
                                <span class="text-xs text-gray-500">Order #{{ $order->id }} · opened {{ $dispute->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="mt-2 font-semibold text-gray-900">{{ $dispute->reasonLabel() }}</h3>
                            <p class="text-sm text-gray-500">{{ $order->ticket?->event?->name }} · {{ $order->ticket?->seat_details }}</p>
                        </div>
                        <div class="text-right">
                            <p class="money text-xl font-bold text-gray-900">KES {{ number_format($order->amount) }}</p>
                            <p class="text-xs text-gray-500">M-Pesa receipt <span class="font-mono">{{ $order->payment?->transaction_id ?? 'N/A' }}</span></p>
                        </div>
                    </div>

                    @if ($order->ticket?->checked_in_at)
                        <x-ui.alert tone="info" class="mt-4">
                            Our scanner admitted this ticket at {{ $order->ticket->checked_in_at->format('D j M, g:i A') }}{{ $order->ticket->checkedInBy ? ' (scanned by '.$order->ticket->checkedInBy->name.')' : '' }}.
                        </x-ui.alert>
                    @endif
                    <blockquote class="mt-4 whitespace-pre-line rounded-xl bg-gray-50 p-4 text-sm text-gray-800 ring-1 ring-gray-100">{{ $dispute->details }}</blockquote>

                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500">Buyer</dt>
                            <dd class="font-medium text-gray-900">{{ $dispute->buyer?->name }}</dd>
                            <dd class="text-gray-500">{{ $dispute->buyer?->email }} · <span class="font-mono">{{ $order->phone_number }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Seller</dt>
                            <dd class="font-medium text-gray-900">{{ $order->ticket?->seller?->name }}</dd>
                            <dd class="text-gray-500">{{ $order->ticket?->seller?->email }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @if ($dispute->evidence_path)
                            <a href="{{ route('admin.disputes.evidence', $dispute) }}" target="_blank" class="inline-flex items-center gap-1.5 font-medium text-brand-600 hover:text-brand-700"><x-ui.icon name="photo" class="h-4 w-4" /> Buyer's evidence</a>
                        @endif
                        @if ($order->ticket?->purchase_proof)
                            <a href="{{ route('tickets.proof', $order->ticket) }}" target="_blank" class="inline-flex items-center gap-1.5 font-medium text-brand-600 hover:text-brand-700"><x-ui.icon name="document" class="h-4 w-4" /> Seller's proof of purchase</a>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-3 border-t border-gray-100 bg-gray-50/60 p-5">
                    @csrf
                    @method('PATCH')
                    <label for="note-{{ $dispute->id }}" class="block text-sm font-medium text-gray-800">Decision note <span class="font-normal text-gray-500">(shown to the buyer)</span></label>
                    <textarea id="note-{{ $dispute->id }}" name="note" rows="2" required maxlength="2000" class="form-control text-sm"
                              placeholder="e.g. The organiser confirmed this ticket was cancelled."></textarea>
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <x-ui.button name="decision" value="release" variant="secondary"
                                     onclick="return confirm('Release KES {{ number_format($order->seller_amount) }} to the seller?')">Release to seller</x-ui.button>
                        <x-ui.button name="decision" value="refund" variant="danger"
                                     onclick="return confirm('Refund KES {{ number_format($order->amount) }} to the buyer?')">Refund buyer</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @empty
            <x-ui.empty-state icon="check-circle" title="No open disputes" description="When a buyer reports a problem it will appear here." />
        @endforelse
    </div>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Recently resolved</h2>
    <x-ui.card padding="p-0">
        @if ($resolved->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-500">Nothing resolved yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="table-th">Order</th><th class="table-th">Reason</th><th class="table-th">Decision</th><th class="table-th">By</th><th class="table-th">When</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($resolved as $dispute)
                            <tr>
                                <td class="table-td">#{{ $dispute->order_id }}</td>
                                <td class="table-td">{{ $dispute->reasonLabel() }}</td>
                                <td class="table-td"><x-ui.status type="dispute" :status="$dispute->status" /></td>
                                <td class="table-td whitespace-nowrap">{{ $dispute->resolver?->name }}</td>
                                <td class="table-td whitespace-nowrap text-gray-500">{{ $dispute->resolved_at?->format('M j, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</x-admin-layout>

<x-admin-layout>
    <x-slot name="title">Payouts & Refunds</x-slot>

    <div class="flex flex-wrap items-baseline justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">Payouts & Refunds</h1>
        <p class="text-sm text-gray-500">Currently held in escrow: <strong class="text-gray-900">KES {{ number_format($heldInEscrow) }}</strong></p>
    </div>

    {{-- Refunds first: these are buyers who paid and got nothing. --}}
    @if ($refunds->isNotEmpty())
        <h2 class="text-lg font-semibold text-red-700 mb-3">Refunds needed ({{ $refunds->count() }})</h2>
        <div class="bg-white shadow-md rounded-lg overflow-x-auto mb-10 border border-red-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Refund to</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">M-Pesa receipt</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Record refund</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($refunds as $order)
                        <tr>
                            <td class="px-6 py-4 text-sm">#{{ $order->id }}<br><span class="text-gray-500">{{ $order->ticket?->event?->name }}</span></td>
                            <td class="px-6 py-4 text-sm">{{ $order->buyer?->name }}<br><span class="text-gray-500">{{ $order->buyer?->email }}</span></td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $order->phone_number }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">KES {{ number_format($order->amount) }}</td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $order->payment?->transaction_id ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('admin.orders.refunded', $order) }}" class="flex justify-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input name="reference" required maxlength="50" placeholder="Refund receipt" aria-label="Refund receipt for order {{ $order->id }}" class="w-36 rounded-md border-gray-300 text-sm">
                                    <button class="bg-red-600 text-white text-sm font-semibold px-3 py-2 rounded-md hover:bg-red-700">Refunded</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-3">Payouts to send ({{ $pending->count() }})</h2>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto mb-10">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pay to</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Record payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($pending as $payout)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $payout->seller?->name }}<br><span class="text-gray-500">{{ $payout->seller?->email }}</span></td>
                        <td class="px-6 py-4 text-sm font-mono">
                            @if ($payout->seller?->payout_phone)
                                {{ $payout->seller->payout_phone }}
                            @else
                                <span class="text-yellow-700 font-sans">No number yet. Ask the seller to add one.</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">Order #{{ $payout->order_id }}<br><span class="text-gray-500">{{ $payout->order?->ticket?->event?->name }}</span></td>
                        <td class="px-6 py-4 text-sm font-semibold">KES {{ number_format($payout->amount) }}</td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('admin.payouts.paid', $payout) }}" class="flex justify-end gap-2">
                                @csrf
                                @method('PATCH')
                                <input name="reference" required maxlength="50" placeholder="M-Pesa receipt" aria-label="M-Pesa receipt for payout {{ $payout->id }}" class="w-36 rounded-md border-gray-300 text-sm">
                                <button class="bg-green-600 text-white text-sm font-semibold px-3 py-2 rounded-md hover:bg-green-700">Paid</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nothing to pay out right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="text-lg font-semibold mb-3">Recently paid</h2>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($recent as $payout)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $payout->seller?->name }}</td>
                        <td class="px-6 py-4 text-sm">KES {{ number_format($payout->amount) }}</td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $payout->reference }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payout->paid_at?->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No payouts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>

<x-admin-layout title="Payouts & refunds">
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat label="Held in escrow" :value="'KES '.number_format($heldInEscrow)" icon="lock" tone="blue" />
        <x-ui.stat label="Payouts to send" :value="'KES '.number_format($pending->where('status', '!=', 'processing')->sum('amount'))" icon="banknotes" tone="amber" :hint="$pending->count().' '.Str::plural('seller payout', $pending->count())" />
        <x-ui.stat label="Refunds to send" :value="'KES '.number_format($refunds->sum('amount'))" icon="warning" :tone="$refunds->isEmpty() ? 'gray' : 'red'" :hint="$refunds->count().' '.Str::plural('order', $refunds->count())" />
    </div>

    <p class="mb-6 text-sm text-gray-600">
        @if ($b2cEnabled)
            Payouts go straight to sellers' M-Pesa. Refunds are still sent from the business account and recorded here.
        @else
            Send the money from the M-Pesa business account, then record the M-Pesa receipt here. (Automatic payouts switch on with <code class="rounded bg-gray-100 px-1 text-xs">MPESA_B2C_ENABLED</code>.)
        @endif
    </p>

    @if ($refunds->isNotEmpty())
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-red-700">Refunds needed · {{ $refunds->count() }}</h2>
        <x-ui.card padding="p-0" class="mb-10 ring-red-200">
            <ul class="divide-y divide-gray-100">
                @foreach ($refunds as $order)
                    <li class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900">{{ $order->buyer?->name }} <span class="font-normal text-gray-500">· Order #{{ $order->id }}</span></p>
                            <p class="text-sm text-gray-500">{{ $order->ticket?->event?->name }} · paid with receipt <span class="font-mono">{{ $order->payment?->transaction_id ?? 'N/A' }}</span></p>
                            <p class="mt-1 text-sm">Refund to <span class="font-mono font-medium text-gray-900">{{ $order->phone_number }}</span></p>
                        </div>
                        <p class="money text-lg font-bold text-gray-900">KES {{ number_format($order->amount) }}</p>
                        <form method="POST" action="{{ route('admin.orders.refunded', $order) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <input name="reference" required maxlength="50" placeholder="Refund receipt" aria-label="Refund receipt for order {{ $order->id }}" class="form-control w-40 text-sm">
                            <x-ui.button variant="danger" size="sm">Mark refunded</x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @php($sendable = $pending->whereIn('status', ['pending'])->filter(fn ($p) => $p->seller?->payout_phone))
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Payouts to send · {{ $pending->count() }}</h2>
        @if ($b2cEnabled && $sendable->count() > 1)
            <form method="POST" action="{{ route('admin.payouts.send-all') }}" onsubmit="return confirm('Send {{ $sendable->count() }} payouts totalling KES {{ number_format($sendable->sum('amount')) }} via M-Pesa?')">
                @csrf
                <x-ui.button variant="mpesa" size="sm">Send all {{ $sendable->count() }} via M-Pesa</x-ui.button>
            </form>
        @endif
    </div>
    <x-ui.card padding="p-0" class="mb-10">
        @if ($pending->isEmpty())
            <x-ui.empty-state icon="check-circle" title="Nothing to pay out" description="Seller payouts appear here once escrow is released after an event." class="border-0" />
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($pending as $payout)
                    <li class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center">
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2 font-medium text-gray-900">
                                {{ $payout->seller?->name }} <span class="font-normal text-gray-500">· Order #{{ $payout->order_id }}</span>
                                @if ($payout->status === 'processing') <x-ui.badge tone="blue" dot>Waiting for M-Pesa</x-ui.badge> @endif
                                @if ($payout->status === 'failed') <x-ui.badge tone="red" dot>Failed</x-ui.badge> @endif
                            </p>
                            <p class="text-sm text-gray-500">{{ $payout->order?->ticket?->event?->name }}</p>
                            <p class="mt-1 text-sm">
                                @if ($payout->seller?->payout_phone)
                                    Pay to <span class="font-mono font-medium text-gray-900">{{ $payout->seller->payout_phone }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-medium text-amber-700"><x-ui.icon name="warning" class="h-4 w-4" /> No payout number. Ask {{ $payout->seller?->email }} to add one.</span>
                                @endif
                            </p>
                            @if ($payout->failure_reason)
                                <p class="mt-1 text-sm text-red-700">{{ $payout->failure_reason }}</p>
                            @endif
                        </div>
                        <p class="money text-lg font-bold text-gray-900">KES {{ number_format($payout->amount) }}</p>
                        <div x-data="{ manual: {{ $b2cEnabled ? 'false' : 'true' }} }" class="flex flex-col items-stretch gap-2 lg:items-end">
                            @if ($b2cEnabled && in_array($payout->status, ['pending', 'failed']) && $payout->seller?->payout_phone)
                                <form method="POST" action="{{ route('admin.payouts.send', $payout) }}">
                                    @csrf
                                    <x-ui.button variant="mpesa" size="sm" class="w-full">{{ $payout->status === 'failed' ? 'Retry' : 'Send' }} via M-Pesa</x-ui.button>
                                </form>
                            @endif
                            @if ($payout->status === 'processing')
                                <form method="POST" action="{{ route('admin.payouts.failed', $payout) }}" onsubmit="return confirm('Only do this if the M-Pesa statement shows the money was NOT sent. Mark as failed?')">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button variant="secondary" size="sm" class="w-full">Didn't go through</x-ui.button>
                                </form>
                            @endif
                            @if ($b2cEnabled)
                                <button type="button" x-show="!manual" @click="manual = true" class="text-xs font-medium text-gray-500 hover:text-gray-900">Paid another way?</button>
                            @endif
                            <form x-show="manual" method="POST" action="{{ route('admin.payouts.paid', $payout) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input name="reference" required maxlength="50" placeholder="M-Pesa receipt" aria-label="M-Pesa receipt for payout {{ $payout->id }}" class="form-control w-40 text-sm">
                                <x-ui.button size="sm" variant="secondary">Mark paid</x-ui.button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Recently paid</h2>
    <x-ui.card padding="p-0">
        @if ($recent->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-500">No payouts yet.</p>
        @else
            <div class="relative overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="table-th">Seller</th><th class="table-th text-right">Amount</th><th class="table-th">Receipt</th><th class="table-th">Paid</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recent as $payout)
                            <tr>
                                <td class="table-td">{{ $payout->seller?->name }}</td>
                                <td class="table-td money text-right font-medium text-gray-900">KES {{ number_format($payout->amount) }}</td>
                                <td class="table-td font-mono">{{ $payout->reference }}</td>
                                <td class="table-td whitespace-nowrap text-gray-500">{{ $payout->paid_at?->format('M j, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</x-admin-layout>

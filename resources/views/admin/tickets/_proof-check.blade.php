{{-- AI reading of the proof of purchase. Advisory: the reviewer decides. --}}
@php($check = $ticket->proofCheck)
@if (\App\Services\ProofCheck\ProofCheckService::enabled() || $check)
    <div class="mt-4 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-200">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                AI check of the proof
                @if (! $check || $check->status === 'pending')
                    <x-ui.badge tone="blue" dot>Checking…</x-ui.badge>
                @elseif ($check->status === 'failed')
                    <x-ui.badge tone="gray" dot>Couldn't check</x-ui.badge>
                @elseif ($check->verdict === 'looks_good')
                    <x-ui.badge tone="green" dot>Matches the listing</x-ui.badge>
                @elseif ($check->verdict === 'unreadable')
                    <x-ui.badge tone="amber" dot>Unreadable</x-ui.badge>
                @else
                    <x-ui.badge tone="red" dot>Needs a closer look</x-ui.badge>
                @endif
            </p>
            @if (\App\Services\ProofCheck\ProofCheckService::enabled())
                <form method="POST" action="{{ route('admin.tickets.proof-check', $ticket) }}">
                    @csrf
                    <button class="text-xs font-medium text-gray-500 hover:text-gray-900">{{ $check ? 'Re-check' : 'Check now' }}</button>
                </form>
            @endif
        </div>

        @if ($check?->status === 'failed')
            <p class="mt-2 text-sm text-gray-600">{{ $check->error }}</p>
        @endif

        @if ($check?->findings)
            <ul class="mt-3 space-y-1.5 text-sm">
                @foreach ($check->findings as $finding)
                    <li class="flex gap-2 {{ $finding['level'] === 'warning' ? 'text-red-800' : 'text-gray-700' }}">
                        <x-ui.icon :name="$finding['level'] === 'warning' ? 'warning' : 'check'" class="mt-0.5 h-4 w-4 {{ $finding['level'] === 'warning' ? 'text-red-500' : 'text-green-600' }}" />
                        <span>{{ $finding['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($check?->extracted)
            <details class="mt-3 text-xs text-gray-600">
                <summary class="cursor-pointer font-medium hover:text-gray-900">What the proof says</summary>
                <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-3">
                    @foreach ($check->extracted as $key => $value)
                        @continue(in_array($key, ['is_ticket_or_receipt', 'readable', 'edit_signs']) || $value === null)
                        <div><dt class="text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt><dd class="text-gray-800">{{ $key === 'price_paid' && is_numeric($value) ? number_format($value) : $value }}</dd></div>
                    @endforeach
                </dl>
                <p class="mt-2 text-gray-400">Read by {{ $check->model }} · {{ $check->updated_at->diffForHumans() }}</p>
            </details>
        @endif
    </div>
@endif

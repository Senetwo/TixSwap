@php
    $kes = fn ($v) => 'KES '.number_format($v);
    // Compact axis labels: 12.5K, 1.2M
    $compact = fn ($v) => $v >= 1_000_000 ? rtrim(rtrim(number_format($v / 1_000_000, 1), '0'), '.').'M' : ($v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1), '0'), '.').'K' : (string) (int) $v);

    // Weekly sales chart geometry (SVG units; scales to the card width).
    $w = 720; $h = 240; $padL = 48; $padR = 8; $padT = 12; $padB = 28;
    $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
    $max = max(1, $weeks->max('total'));
    // A "nice" axis top and 4 gridlines.
    $mag = 10 ** floor(log10($max));
    $niceMax = ceil($max / $mag * 2) / 2 * $mag;
    $ticks = collect(range(0, 4))->map(fn ($i) => $niceMax / 4 * $i);
    $band = $plotW / $weeks->count();
    $barW = min(24, $band - 8);
@endphp
<x-admin-layout title="Analytics">
    {{-- Time range: one control row above everything --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">How the marketplace is doing. Sales exclude refunded orders.</p>
        <nav class="flex gap-1 rounded-xl bg-gray-100 p-1" aria-label="Time range">
            @foreach ($ranges as $value => $label)
                <a href="{{ route('admin.analytics.index', ['days' => $value]) }}" @class([
                    'rounded-lg px-3 py-1.5 text-sm font-medium',
                    'bg-white text-gray-900 shadow-sm' => $days === $value,
                    'text-gray-600 hover:text-gray-900' => $days !== $value,
                ]) @if ($days === $value) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Sales" :value="$kes($kpis['sales'])" :hint="number_format($kpis['orders']).' paid '.Str::plural('order', $kpis['orders']).' · avg '.$kes($kpis['aov'])" />
        <x-ui.stat label="TixSwap revenue" :value="$kes($kpis['revenue'])" hint="Commission on those sales" />
        <x-ui.stat label="Checkout conversion" :value="$kpis['conversion'] === null ? '-' : $kpis['conversion'].'%'"
                   :hint="$kpis['started'] ? number_format($kpis['orders']).' of '.number_format($kpis['started']).' checkouts paid' : 'No checkouts yet'" />
        <x-ui.stat label="Dispute rate" :value="$kpis['disputeRate'] === null ? '-' : $kpis['disputeRate'].'%'"
                   :hint="$kpis['disputes'].' '.Str::plural('dispute', $kpis['disputes']).' · '.$kpis['refunded'].' refunded'" />
    </div>

    {{-- Weekly sales: single series, so the title names it and there's no legend --}}
    <x-ui.card class="mt-6" title="Weekly sales" description="Last 12 weeks, paid orders in KES">
        <figure>
            <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-auto w-full" role="img" aria-labelledby="weekly-sales-desc">
                <desc id="weekly-sales-desc">Bar chart of weekly sales over the last 12 weeks. The highest week was {{ $kes($weeks->max('total')) }}. Full figures are in the table below the chart.</desc>
                {{-- Recessive hairline grid + y labels --}}
                @foreach ($ticks as $t)
                    @php
                        $y = $padT + $plotH - ($t / $niceMax) * $plotH;
                    @endphp
                    <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $y }}" y2="{{ $y }}" stroke="#e7e5e4" stroke-width="1" />
                    <text x="{{ $padL - 8 }}" y="{{ $y + 4 }}" text-anchor="end" class="fill-gray-500" font-size="11">{{ $compact($t) }}</text>
                @endforeach
                @foreach ($weeks as $i => $week)
                    @php
                        $bh = $week['total'] > 0 ? max(2, ($week['total'] / $niceMax) * $plotH) : 0;
                        $x = $padL + $band * $i + ($band - $barW) / 2;
                        $y = $padT + $plotH - $bh;
                        $r = min(4, $bh);
                        $label = 'Week of '.$week['start']->format('M j').': '.$kes($week['total']).', '.$week['orders'].' '.Str::plural('order', $week['orders']);
                    @endphp
                    <g class="group" tabindex="0" aria-label="{{ $label }}">
                        <title>{{ $label }}</title>
                        {{-- Hit target spans the whole band, larger than the bar --}}
                        <rect x="{{ $padL + $band * $i }}" y="{{ $padT }}" width="{{ $band }}" height="{{ $plotH }}" fill="transparent" />
                        @if ($bh > 0)
                            {{-- 4px rounded data-end, square at the baseline --}}
                            <path d="M{{ $x }},{{ $y + $bh }} V{{ $y + $r }} Q{{ $x }},{{ $y }} {{ $x + $r }},{{ $y }} H{{ $x + $barW - $r }} Q{{ $x + $barW }},{{ $y }} {{ $x + $barW }},{{ $y + $r }} V{{ $y + $bh }} Z"
                                  fill="#2a78d6" class="transition-opacity group-hover:opacity-80 group-focus:opacity-80" />
                        @endif
                        @if ($i % 2 === 1 || $weeks->count() <= 6)
                            <text x="{{ $padL + $band * $i + $band / 2 }}" y="{{ $h - 8 }}" text-anchor="middle" class="fill-gray-500" font-size="11">{{ $week['start']->format('M j') }}</text>
                        @endif
                    </g>
                @endforeach
                <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $padT + $plotH }}" y2="{{ $padT + $plotH }}" stroke="#d6d3d1" stroke-width="1" />
            </svg>
            <details class="mt-3 text-sm">
                <summary class="cursor-pointer font-medium text-gray-600 hover:text-gray-900">Show as table</summary>
                <table class="mt-2 min-w-full text-left">
                    <thead><tr><th class="py-1.5 pr-4 font-medium text-gray-500">Week of</th><th class="py-1.5 pr-4 text-right font-medium text-gray-500">Orders</th><th class="py-1.5 text-right font-medium text-gray-500">Sales</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($weeks->reverse() as $week)
                            <tr><td class="py-1.5 pr-4 text-gray-700">{{ $week['start']->format('M j, Y') }}</td><td class="money py-1.5 pr-4 text-right text-gray-700">{{ $week['orders'] }}</td><td class="money py-1.5 text-right text-gray-900">{{ $kes($week['total']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </figure>
    </x-ui.card>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Two small breakdowns: numbers, not charts --}}
        <x-ui.card title="Checkouts" description="What happened to checkouts started in this period">
            @php
                $checkoutRows = ['paid' => 'Paid', 'failed' => 'Cancelled or failed', 'expired' => 'Ran out of time', 'pending' => 'Still waiting', 'needs_refund' => 'Paid too late (refund)', 'refunded' => 'Refunded'];
            @endphp
            <dl class="divide-y divide-gray-100 text-sm">
                @foreach ($checkoutRows as $key => $label)
                    @continue(! ($outcomes[$key] ?? 0) && ! in_array($key, ['paid', 'failed', 'expired']))
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-gray-600">{{ $label }}</dt>
                        <dd class="money font-semibold text-gray-900">{{ number_format($outcomes[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card title="Listings" description="Submitted in this period">
            @php($submitted = $listings->sum())
            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex items-center justify-between py-2"><dt class="text-gray-600">Submitted</dt><dd class="money font-semibold text-gray-900">{{ number_format($submitted) }}</dd></div>
                <div class="flex items-center justify-between py-2"><dt class="text-gray-600">Approved (live or sold)</dt><dd class="money font-semibold text-gray-900">{{ number_format(($listings['approved'] ?? 0) + ($listings['reserved'] ?? 0) + ($listings['sold'] ?? 0)) }}</dd></div>
                <div class="flex items-center justify-between py-2"><dt class="text-gray-600">Rejected</dt><dd class="money font-semibold text-gray-900">{{ number_format($listings['rejected'] ?? 0) }}</dd></div>
                <div class="flex items-center justify-between py-2"><dt class="text-gray-600">Waiting for review</dt><dd class="money font-semibold text-gray-900">{{ number_format($listings['pending'] ?? 0) }}</dd></div>
                <div class="flex items-center justify-between py-2"><dt class="text-gray-600">Median time to review</dt><dd class="money font-semibold text-gray-900">{{ $kpis['reviewHours'] === null ? '-' : $kpis['reviewHours'].' h' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Top events" description="By sales in this period" padding="p-0">
        @if ($topEvents->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-500">No sales in this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="table-th">Event</th><th class="table-th text-right">Tickets sold</th><th class="table-th text-right">Sales</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($topEvents as $row)
                            <tr>
                                <td class="table-td">
                                    <p class="font-medium text-gray-900">{{ $row['event']?->name ?? 'Deleted event' }}</p>
                                    @if ($row['event']) <p class="text-xs text-gray-500">{{ $row['event']->date->format('M j, Y') }} · {{ $row['event']->location }}</p> @endif
                                </td>
                                <td class="table-td money text-right">{{ number_format($row['tickets']) }}</td>
                                <td class="table-td money text-right font-medium text-gray-900">{{ $kes($row['sales']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</x-admin-layout>

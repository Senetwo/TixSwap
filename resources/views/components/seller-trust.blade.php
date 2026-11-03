@props(['seller'])
@if ($seller)
    @php($stats = $seller->sellerStats())
    <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }}>
        @if ($seller->isTrustedSeller())
            <x-ui.badge tone="green"><x-ui.icon name="shield-check" class="h-3.5 w-3.5" /> Trusted seller</x-ui.badge>
        @elseif ($seller->isNewSeller())
            <x-ui.badge>New seller</x-ui.badge>
        @endif
        @if ($stats['ratings'])
            <span class="text-sm text-gray-500">{{ $stats['percent'] }}% positive · {{ $stats['ratings'] }} {{ Str::plural('rating', $stats['ratings']) }}</span>
        @endif
    </span>
@endif

@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'gray', 'href' => null])
@php
    $iconTones = ['gray' => 'bg-gray-100 text-gray-600', 'green' => 'bg-green-50 text-green-600', 'amber' => 'bg-amber-50 text-amber-600', 'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600', 'red' => 'bg-red-50 text-red-600'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'block rounded-2xl bg-white p-5 shadow-card ring-1 ring-gray-200/70'.($href ? ' transition hover:shadow-lift hover:ring-gray-300' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        @if ($icon)
            <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $iconTones[$tone] ?? $iconTones['gray'] }}"><x-ui.icon :name="$icon" /></span>
        @endif
    </div>
    <p class="money mt-1 text-2xl font-bold tracking-tight text-gray-900">{{ $value }}</p>
    @if ($hint) <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p> @endif
    {{ $slot }}
</{{ $tag }}>

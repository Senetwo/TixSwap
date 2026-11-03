@props(['tone' => 'info', 'title' => null, 'dismissible' => false])
@php
    $tones = [
        'info' => ['bg-blue-50 ring-blue-200 text-blue-900', 'info', 'text-blue-500'],
        'success' => ['bg-green-50 ring-green-200 text-green-900', 'check-circle', 'text-green-600'],
        'warning' => ['bg-amber-50 ring-amber-200 text-amber-900', 'warning', 'text-amber-500'],
        'error' => ['bg-red-50 ring-red-200 text-red-900', 'warning', 'text-red-500'],
    ];
    [$box, $icon, $iconColor] = $tones[$tone] ?? $tones['info'];
@endphp
<div @if ($dismissible) x-data="{ open: true }" x-show="open" x-transition.opacity @endif
     role="{{ $tone === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->merge(['class' => 'flex gap-3 rounded-xl p-4 text-sm ring-1 ring-inset '.$box]) }}>
    <x-ui.icon :name="$icon" class="h-5 w-5 {{ $iconColor }}" />
    <div class="min-w-0 flex-1">
        @if ($title) <p class="font-semibold">{{ $title }}</p> @endif
        <div @class(['mt-1' => $title])>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="open = false" class="-m-1 rounded p-1 opacity-60 hover:opacity-100" aria-label="Dismiss">
            <x-ui.icon name="x-mark" class="h-4 w-4" />
        </button>
    @endif
</div>

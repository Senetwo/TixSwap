@props([
    'variant' => 'primary', // primary | secondary | ghost | danger | mpesa | dark
    'size' => 'md',         // sm | md | lg
    'href' => null,
    'icon' => null,
    'iconRight' => null,
])
@php
    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800',
        'secondary' => 'bg-white text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
        'ghost' => 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'mpesa' => 'bg-mpesa-600 text-white shadow-sm hover:bg-mpesa-700 active:bg-mpesa-700',
        'dark' => 'bg-gray-900 text-white shadow-sm hover:bg-gray-800',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-5 py-3 text-base gap-2',
    ];
    $classes = 'inline-flex items-center justify-center rounded-lg font-semibold transition-colors duration-150 '
        .'disabled:cursor-not-allowed disabled:opacity-60 '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
    $iconSize = $size === 'lg' ? 'h-5 w-5' : 'h-4 w-4';
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-ui.icon :name="$icon" :class="$iconSize" /> @endif
        {{ $slot }}
        @if ($iconRight) <x-ui.icon :name="$iconRight" :class="$iconSize" /> @endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        @if ($icon) <x-ui.icon :name="$icon" :class="$iconSize" /> @endif
        {{ $slot }}
        @if ($iconRight) <x-ui.icon :name="$iconRight" :class="$iconSize" /> @endif
    </button>
@endif

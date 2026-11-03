@props(['tone' => 'gray', 'dot' => false])
@php
    $tones = [
        'gray' => 'bg-gray-100 text-gray-700 ring-gray-500/10',
        'green' => 'bg-green-50 text-green-700 ring-green-600/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'red' => 'bg-red-50 text-red-700 ring-red-600/10',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-600/10',
    ];
    $dots = ['gray' => 'bg-gray-400', 'green' => 'bg-green-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'blue' => 'bg-blue-500', 'brand' => 'bg-brand-500'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.($tones[$tone] ?? $tones['gray'])]) }}>
    @if ($dot) <span class="h-1.5 w-1.5 rounded-full {{ $dots[$tone] ?? $dots['gray'] }}" aria-hidden="true"></span> @endif
    {{ $slot }}
</span>

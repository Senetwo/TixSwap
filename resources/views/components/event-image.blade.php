@props(['event', 'rounded' => ''])
@php
    // Show the uploaded cover if it exists; otherwise a branded gradient
    // (stable per event) instead of a broken-image icon.
    $path = $event?->image_url;
    $exists = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    $gradients = [
        'from-brand-600 to-orange-500',
        'from-violet-600 to-fuchsia-500',
        'from-sky-600 to-cyan-500',
        'from-emerald-600 to-teal-500',
        'from-amber-500 to-rose-500',
        'from-indigo-600 to-blue-500',
    ];
    $gradient = $gradients[($event?->id ?? 0) % count($gradients)];
@endphp
@if ($exists)
    <img src="{{ asset('storage/'.$path) }}" alt="" loading="lazy" {{ $attributes->merge(['class' => 'object-cover bg-gray-100 '.$rounded]) }}>
@else
    <div {{ $attributes->merge(['class' => 'relative flex items-center justify-center overflow-hidden bg-gradient-to-br '.$gradient.' '.$rounded]) }} aria-hidden="true">
        <svg class="absolute inset-0 h-full w-full opacity-[0.15]" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="dots-{{ $event?->id }}" width="16" height="16" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="#fff"/></pattern></defs><rect width="100%" height="100%" fill="url(#dots-{{ $event?->id }})"/></svg>
        <x-application-logo class="relative h-1/3 max-h-16 w-auto text-white/90" />
    </div>
@endif

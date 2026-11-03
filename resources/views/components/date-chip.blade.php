@props(['date'])
@php($d = \Illuminate\Support\Carbon::parse($date))
<div {{ $attributes->merge(['class' => 'flex w-12 flex-col items-center rounded-lg bg-white/95 py-1 text-center shadow-sm backdrop-blur']) }}>
    <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600">{{ $d->format('M') }}</span>
    <span class="text-lg font-bold leading-none text-gray-900">{{ $d->format('j') }}</span>
</div>

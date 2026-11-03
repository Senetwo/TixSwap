@props(['title', 'description' => null, 'back' => null, 'backLabel' => 'Back'])
<div {{ $attributes->merge(['class' => 'mb-8']) }}>
    @if ($back)
        <a href="{{ $back }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> {{ $backLabel }}
        </a>
    @endif
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
            @if ($description) <p class="mt-1.5 max-w-2xl text-gray-600">{{ $description }}</p> @endif
        </div>
        @isset($actions) <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div> @endisset
    </div>
</div>

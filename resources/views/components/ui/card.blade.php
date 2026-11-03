@props(['padding' => 'p-6', 'title' => null, 'description' => null])
<section {{ $attributes->merge(['class' => 'rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70']) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-6 py-4">
            <div>
                @if ($title) <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2> @endif
                @if ($description) <p class="mt-0.5 text-sm text-gray-500">{{ $description }}</p> @endif
            </div>
            @isset($actions) <div class="flex items-center gap-2">{{ $actions }}</div> @endisset
        </header>
    @endif
    <div class="{{ $padding }}">{{ $slot }}</div>
</section>

@props(['icon' => 'inbox', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-2xl border-2 border-dashed border-gray-200 bg-white px-6 py-14 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500"><x-ui.icon :name="$icon" class="h-6 w-6" /></span>
    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ $title }}</h3>
    @if ($description) <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $description }}</p> @endif
    @if (trim($slot) !== '') <div class="mt-6 flex flex-wrap justify-center gap-3">{{ $slot }}</div> @endif
</div>

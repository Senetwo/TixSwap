{{-- A form section: title + description on the left, fields on the right (stacked on mobile). --}}
<div class="grid grid-cols-1 gap-6 py-8 first:pt-0 last:pb-0 md:grid-cols-3">
    <div>
        <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">{{ $step }}</span>
            {{ $title }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    </div>
    <div class="space-y-5 md:col-span-2">{{ $slot }}</div>
</div>

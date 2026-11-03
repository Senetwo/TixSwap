@php($maskId = 'tixswap-logo-'.\Illuminate\Support\Str::random(6))
{{-- TixSwap mark: a ticket with swap arrows. Uses currentColor, so set the colour with a text-* class. --}}
<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="TixSwap" {{ $attributes }}>
    <defs>
        <mask id="{{ $maskId }}">
            <rect width="64" height="64" fill="#fff"/>
            <g fill="none" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 26h22m-6-6 6 6-6 6"/>
                <path d="M44 38H22m6-6-6 6 6 6"/>
            </g>
        </mask>
    </defs>
    <path mask="url(#{{ $maskId }})" fill="currentColor"
          d="M12 14h40a6 6 0 0 1 6 6v6a6 6 0 0 0 0 12v6a6 6 0 0 1-6 6H12a6 6 0 0 1-6-6v-6a6 6 0 0 0 0-12v-6a6 6 0 0 1 6-6z"/>
</svg>

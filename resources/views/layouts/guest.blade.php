<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <x-partials-head :title="$title ?? null" />
</head>
<body class="h-full bg-white">
    <div class="flex min-h-full">
        {{-- Brand panel (desktop) --}}
        <aside class="relative hidden w-[44%] max-w-xl flex-col justify-between overflow-hidden bg-gray-950 p-12 text-white lg:flex">
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/40 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-orange-500/20 blur-3xl" aria-hidden="true"></div>

            <a href="{{ route('login') }}" class="relative flex items-center gap-2 text-xl font-bold">
                <x-application-logo class="h-9 w-9 text-brand-500" /> TixSwap
            </a>

            <div class="relative">
                <h2 class="text-4xl font-bold leading-tight tracking-tight">Can't make it? Sell it.<br>Missed out? Buy it, safely.</h2>
                <p class="mt-4 text-lg text-gray-300">The ticket resale marketplace built for Kenya.</p>

                <ul class="mt-10 space-y-6">
                    @foreach ([
                        ['shield-check', 'Every listing checked', 'Our team reviews each ticket and its proof of purchase before it goes live.'],
                        ['phone', 'Pay with M-Pesa', 'Approve the payment on your phone. We never ask for your PIN.'],
                        ['lock', 'Escrow protection', 'Sellers get paid after the event. If the ticket doesn\'t work, you get your money back.'],
                    ] as [$icon, $heading, $text])
                        <li class="flex gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15"><x-ui.icon :name="$icon" class="h-5 w-5 text-white" /></span>
                            <div>
                                <p class="font-semibold">{{ $heading }}</p>
                                <p class="mt-0.5 text-sm text-gray-400">{{ $text }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-sm text-gray-500">© {{ date('Y') }} TixSwap · <a href="{{ route('terms') }}" class="hover:text-gray-300">Terms</a> · <a href="{{ route('privacy') }}" class="hover:text-gray-300">Privacy</a></p>
        </aside>

        {{-- Form --}}
        <main class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:px-16">
            <div class="mx-auto w-full max-w-sm">
                <a href="{{ route('login') }}" class="mb-10 flex items-center gap-2 text-xl font-bold text-gray-900 lg:hidden">
                    <x-application-logo class="h-9 w-9 text-brand-600" /> TixSwap
                </a>
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>

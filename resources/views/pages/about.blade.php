<x-tixswap-layout title="About" width="max-w-5xl">
    <section class="py-6 text-center sm:py-10">
        <x-application-logo class="mx-auto h-14 w-14 text-brand-600" />
        <h1 class="mx-auto mt-6 max-w-2xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">Ticket resale, without the risk</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
            Plans change. TixSwap lets fans sell tickets they can't use to fans who missed out, with checks and protection that WhatsApp groups and street touts don't offer.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @guest
                <x-ui.button size="lg" :href="route('register')">Create a free account</x-ui.button>
                <x-ui.button size="lg" variant="secondary" :href="route('login')">Log in</x-ui.button>
            @else
                <x-ui.button size="lg" :href="route('dashboard')">Browse tickets</x-ui.button>
            @endguest
        </div>
    </section>

    <section class="mt-12 grid gap-6 md:grid-cols-3">
        @foreach ([
            ['clipboard-check', 'Every listing is checked', 'Sellers upload proof of purchase. Our team reviews it, and flags tickets that have been listed before, before anything goes on sale.'],
            ['phone', 'Pay with M-Pesa', 'Buyers approve the payment on their own phone with an STK prompt. TixSwap never asks for your PIN.'],
            ['lock', 'Money held in escrow', 'We hold the payment until after the event. If a ticket doesn\'t work, the buyer reports it and gets a refund.'],
        ] as [$icon, $title, $text])
            <x-ui.card>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-ui.icon :name="$icon" class="h-6 w-6" /></span>
                <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ $title }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ $text }}</p>
            </x-ui.card>
        @endforeach
    </section>

    <section class="mt-16 grid gap-10 md:grid-cols-2">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">For buyers</h2>
            <ol class="mt-6 space-y-5">
                @foreach (['Find your event and check the seat details.', 'Pay with M-Pesa. Your ticket and QR code appear instantly.', 'Show the QR code at the gate. If there\'s a problem, report it and we\'ll refund you.'] as $i => $step)
                    <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">{{ $i + 1 }}</span><p class="pt-1 text-gray-700">{{ $step }}</p></li>
                @endforeach
            </ol>
        </div>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">For sellers</h2>
            <ol class="mt-6 space-y-5">
                @foreach (['List your ticket with a photo of your receipt or e-ticket.', 'We review it, usually within a few hours, and it goes live.', 'When it sells, you\'re paid to M-Pesa after the event, minus a '.rtrim(rtrim(number_format(config('services.tixswap.commission_percent'), 1), '0'), '.').'% fee.'] as $i => $step)
                    <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">{{ $i + 1 }}</span><p class="pt-1 text-gray-700">{{ $step }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>
</x-tixswap-layout>

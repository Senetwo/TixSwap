@php($fee = rtrim(rtrim(number_format(config('services.tixswap.commission_percent'), 1), '0'), '.'))
<x-tixswap-layout title="Help & support" width="max-w-3xl">
    <x-ui.page-header title="Help & support" description="Answers to common questions. Can't find what you need? Email us." />

    @php($faqs = [
        'Buying' => [
            ['How do I pay?', 'Enter your Safaricom number on the ticket page and tap Pay. You\'ll get an M-Pesa prompt on your phone; enter your PIN there. TixSwap will never ask for your PIN on the website.'],
            ['Where is my ticket?', 'It appears straight after payment, and is always in My tickets. Show the QR code at the entrance. You can save it as an image in case you have no signal at the venue.'],
            ['What if the ticket doesn\'t work?', 'Open the ticket in My tickets and tap "Report it". The seller\'s payment is held until we\'ve reviewed it, and if the ticket wasn\'t valid we refund you to M-Pesa. You can report a problem until 24 hours after the event.'],
            ['I paid but didn\'t get a ticket', 'This can happen if the ticket was bought by someone else while your payment was going through. You\'ll be refunded in full; our team handles these automatically.'],
        ],
        'Selling' => [
            ['How do I sell a ticket?', 'Create a seller account, choose "Sell a ticket", and add the event details, your price and a photo of your receipt or e-ticket. We check it, usually within a few hours, before it goes live.'],
            ['When do I get paid?', 'Buyers\' money is held in escrow until 24 hours after the event, then sent to the M-Pesa number in your account settings, minus a '.$fee.'% fee.'],
            ['Why was my listing rejected?', 'The reason is shown on your listing. Most often the proof of purchase was unclear or didn\'t match the ticket details. You\'re welcome to list it again with a clearer photo.'],
        ],
    ])

    <div class="space-y-10">
        @foreach ($faqs as $group => $items)
            <section>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $group }}</h2>
                <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70">
                    @foreach ($items as [$q, $a])
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-medium text-gray-900 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                                {{ $q }}
                                <x-ui.icon name="chevron-down" class="h-5 w-5 text-gray-400 transition group-open:rotate-180" />
                            </summary>
                            <p class="px-5 pb-5 text-gray-600">{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach

        <x-ui.card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-ui.icon name="chat" class="h-6 w-6" /></span>
                <div class="flex-1">
                    <h2 class="font-semibold text-gray-900">Still need help?</h2>
                    <p class="text-sm text-gray-600">Email us and include your order number if you have one. We reply within one working day.</p>
                </div>
                <x-ui.button href="mailto:support@tixswap.com">Email support</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-tixswap-layout>

<x-tixswap-layout title="You're going!" width="max-w-xl">
    <div class="text-center">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-mpesa-50 text-mpesa-600 ring-8 ring-mpesa-50/60">
            <x-ui.icon name="check" class="h-8 w-8" />
        </span>
        <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Payment received. You're going!</h1>
        <p class="mt-2 text-gray-600">Your ticket is ready. It's also saved in <a href="{{ route('cart.index') }}" class="font-medium text-brand-600 hover:text-brand-700">My tickets</a>.</p>
    </div>

    <x-ticket-pass :ticket="$ticket" :qr-code="$qrCode" class="mt-8" />

    <div class="mx-auto mt-6 grid max-w-sm grid-cols-1 gap-3 sm:grid-cols-2">
        @include('partials.save-ticket', ['filename' => 'TixSwap-ticket-'.$ticket->id.'.png'])
        <x-ui.button :href="route('cart.index')">My tickets</x-ui.button>
    </div>

    <x-ui.alert tone="info" class="mx-auto mt-8 max-w-sm" title="You're protected">
        The seller is paid after the event. If the ticket doesn't work, report it from My tickets and we'll refund you.
    </x-ui.alert>
</x-tixswap-layout>

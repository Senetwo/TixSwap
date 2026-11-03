<x-tixswap-layout :title="$ticket->event->name" width="max-w-xl">
    <a href="{{ route('cart.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> My tickets
    </a>

    <x-ticket-pass :ticket="$ticket" :qr-code="$qrCode" />

    <div class="mx-auto mt-6 max-w-sm space-y-6">
        @include('partials.save-ticket', ['filename' => 'TixSwap-ticket-'.$ticket->id.'.png'])
        @include('disputes._status', ['order' => $order])
        @include('ratings._form', ['order' => $order])
    </div>
</x-tixswap-layout>

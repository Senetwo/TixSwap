<x-guest-layout title="Confirm password">
    @include('auth._heading', ['heading' => 'Confirm your password', 'sub' => 'This is a secure area. Please confirm your password to continue.'])

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        <x-ui.field label="Password" name="password" type="password" required autocomplete="current-password" />
        <x-ui.button size="lg" class="w-full">Confirm</x-ui.button>
    </form>
</x-guest-layout>

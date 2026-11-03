<x-guest-layout title="Choose a new password">
    @include('auth._heading', ['heading' => 'Choose a new password'])

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-ui.field label="Email" name="email" type="email" :value="$request->email" required autofocus autocomplete="username" />
        <x-ui.field label="New password" name="password" type="password" required autocomplete="new-password" hint="At least 8 characters." />
        <x-ui.field label="Confirm new password" name="password_confirmation" type="password" required autocomplete="new-password" />
        <x-ui.button size="lg" class="w-full">Reset password</x-ui.button>
    </form>
</x-guest-layout>

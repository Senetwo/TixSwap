<x-guest-layout title="Reset password">
    @include('auth._heading', ['heading' => 'Forgot your password?', 'sub' => 'Enter your email and we\'ll send you a link to choose a new one.'])

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-ui.field label="Email" name="email" type="email" required autofocus autocomplete="username" />
        <x-ui.button size="lg" class="w-full">Email me a reset link</x-ui.button>
        <p class="text-center text-sm"><a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-gray-900">Back to log in</a></p>
    </form>
</x-guest-layout>

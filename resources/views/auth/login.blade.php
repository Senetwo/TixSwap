<x-guest-layout title="Log in">
    @include('auth._heading', ['heading' => 'Welcome back', 'sub' => 'New to TixSwap? <a href="'.route('register').'" class="font-semibold text-brand-600 hover:text-brand-700">Create an account</a>'])

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-ui.field label="Email" name="email" type="email" required autofocus autocomplete="username" />
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-gray-800">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Forgot password?</a>
                @endif
            </div>
            <x-ui.field name="password" type="password" required autocomplete="current-password" class="mt-1.5" />
        </div>
        <label for="remember_me" class="flex items-center gap-2 text-sm text-gray-600">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
            Keep me logged in
        </label>
        <x-ui.button size="lg" class="w-full">Log in</x-ui.button>
    </form>
</x-guest-layout>

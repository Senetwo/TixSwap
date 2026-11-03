<x-guest-layout title="Create account">
    @include('auth._heading', ['heading' => 'Create your account', 'sub' => 'Already have one? <a href="'.route('login').'" class="font-semibold text-brand-600 hover:text-brand-700">Log in</a>'])

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <fieldset>
            <legend class="text-sm font-medium text-gray-800">I want to…</legend>
            <div class="mt-2 grid grid-cols-2 gap-3">
                @foreach (['buyer' => ['ticket', 'Buy tickets', 'Find verified tickets'], 'seller' => ['banknotes', 'Sell tickets', 'Get paid via M-Pesa']] as $value => [$icon, $label, $text])
                    <label class="relative flex cursor-pointer flex-col rounded-xl border border-gray-200 p-4 transition hover:bg-gray-50 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50 has-[:checked]:ring-1 has-[:checked]:ring-gray-900">
                        <input type="radio" name="role" value="{{ $value }}" class="sr-only" @checked(old('role', 'buyer') === $value) required>
                        <x-ui.icon :name="$icon" class="h-6 w-6 text-brand-600" />
                        <span class="mt-3 text-sm font-semibold text-gray-900">{{ $label }}</span>
                        <span class="mt-0.5 text-xs text-gray-500">{{ $text }}</span>
                    </label>
                @endforeach
            </div>
            @error('role') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </fieldset>

        <x-ui.field label="Full name" name="name" required autofocus autocomplete="name" />
        <x-ui.field label="Email" name="email" type="email" required autocomplete="username" />
        <x-ui.field label="Password" name="password" type="password" required autocomplete="new-password" hint="At least 8 characters." />
        <x-ui.field label="Confirm password" name="password_confirmation" type="password" required autocomplete="new-password" />

        <x-ui.button size="lg" class="w-full">Create account</x-ui.button>
        <p class="text-center text-xs text-gray-500">
            By creating an account you agree to our <a href="{{ route('terms') }}" class="underline hover:text-gray-700">Terms</a> and <a href="{{ route('privacy') }}" class="underline hover:text-gray-700">Privacy Policy</a>.
        </p>
    </form>
</x-guest-layout>

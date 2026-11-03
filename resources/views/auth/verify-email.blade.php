<x-guest-layout title="Verify your email">
    <span class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600"><x-ui.icon name="inbox" class="h-6 w-6" /></span>
    @include('auth._heading', ['heading' => 'Check your inbox', 'sub' => 'We sent a verification link to <strong class="text-gray-900">'.e(auth()->user()->email).'</strong>. Click it to finish setting up your account.'])

    @if (session('status') == 'verification-link-sent')
        <x-ui.alert tone="success" class="mb-6">A new verification link has been sent.</x-ui.alert>
    @endif

    <div class="space-y-3">
        @if (app()->isLocal())
            <form method="POST" action="{{ route('verification.force_verify') }}">
                @csrf
                <x-ui.button size="lg" class="w-full">Verify this email (dev only)</x-ui.button>
            </form>
        @else
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.button size="lg" class="w-full">Resend verification email</x-ui.button>
            </form>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button variant="ghost" class="w-full">Log out</x-ui.button>
        </form>
    </div>
</x-guest-layout>

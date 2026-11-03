<x-ui.card title="Your details" description="Your name is shown to buyers and sellers you trade with.">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-ui.field label="Full name" name="name" :value="$user->name" required autocomplete="name" />
            <x-ui.field label="Email" name="email" type="email" :value="$user->email" required autocomplete="username" />
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <x-ui.alert tone="warning">
                Your email address isn't verified yet.
                <button form="send-verification" class="font-semibold underline">Resend the verification email</button>
                @if (session('status') === 'verification-link-sent')
                    <span class="mt-1 block font-medium text-green-700">A new verification link has been sent.</span>
                @endif
            </x-ui.alert>
        @endif

        @if ($user->isSeller())
            <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-200">
                <x-ui.field label="Payout M-Pesa number" name="payout_phone" type="tel" inputmode="tel" autocomplete="tel"
                            :value="$user->payout_phone ? '0'.substr($user->payout_phone, 3) : ''" placeholder="0712 345 678"
                            hint="Your earnings are sent to this number after each event." />
            </div>
        @endif

        <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-5">
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="flex items-center gap-1.5 text-sm font-medium text-green-700">
                    <x-ui.icon name="check" class="h-4 w-4" /> Saved
                </p>
            @endif
            <x-ui.button>Save changes</x-ui.button>
        </div>
    </form>
</x-ui.card>

<x-ui.card title="Password" description="Use a long password you don't use anywhere else.">
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        @php($e = $errors->updatePassword)
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            @foreach ([['current_password', 'Current password', 'current-password'], ['password', 'New password', 'new-password'], ['password_confirmation', 'Confirm new password', 'new-password']] as [$field, $label, $ac])
                <div>
                    <label for="update_password_{{ $field }}" class="block text-sm font-medium text-gray-800">{{ $label }}</label>
                    <input id="update_password_{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $ac }}"
                           @class(['form-control mt-1.5', 'form-control-error' => $e->has($field)])>
                    @if ($e->has($field)) <p class="mt-1.5 text-sm text-red-600">{{ $e->first($field) }}</p> @endif
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-5">
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="flex items-center gap-1.5 text-sm font-medium text-green-700">
                    <x-ui.icon name="check" class="h-4 w-4" /> Password updated
                </p>
            @endif
            <x-ui.button>Update password</x-ui.button>
        </div>
    </form>
</x-ui.card>

<x-ui.card title="Delete account" description="Permanently delete your account and data. This can't be undone." class="ring-red-200/80">
    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm ring-1 ring-inset ring-red-200 hover:bg-red-50">
        <x-ui.icon name="trash" class="h-4 w-4" /> Delete account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')
            <h2 class="text-lg font-semibold text-gray-900">Delete your account?</h2>
            <p class="mt-2 text-sm text-gray-600">Your account and its data will be permanently deleted. Enter your password to confirm.</p>

            <div class="mt-5">
                <label for="password" class="sr-only">Password</label>
                <input id="password" name="password" type="password" placeholder="Password"
                       @class(['form-control', 'form-control-error' => $errors->userDeletion->has('password')])>
                @if ($errors->userDeletion->has('password'))
                    <p class="mt-1.5 text-sm text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-danger-button>Delete account</x-danger-button>
            </div>
        </form>
    </x-modal>
</x-ui.card>

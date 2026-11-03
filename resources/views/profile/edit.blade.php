<x-tixswap-layout title="Account settings" width="max-w-4xl">
    <x-ui.page-header title="Account settings" description="Manage your details, payout number and password." />

    <div class="space-y-6">
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
</x-tixswap-layout>

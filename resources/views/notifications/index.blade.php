<x-tixswap-layout title="Notifications" width="max-w-2xl">
    <x-ui.page-header title="Notifications">
        @if (auth()->user()->unreadNotifications()->exists())
            <x-slot:actions>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-ui.button variant="secondary" size="sm" icon="check">Mark all as read</x-ui.button>
                </form>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($notifications->isEmpty())
        <x-ui.empty-state icon="inbox" title="No notifications yet" description="We'll let you know about sales, payments and anything that needs your attention." />
    @else
        <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-gray-200/70">
            @foreach ($notifications as $notification)
                @include('notifications._item')
            @endforeach
        </div>
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</x-tixswap-layout>

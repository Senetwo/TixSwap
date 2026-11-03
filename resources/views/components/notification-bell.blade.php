@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $latest = $user->notifications()->limit(6)->get();
@endphp
<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true"
            class="relative rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900"
            aria-label="Notifications{{ $unread ? ', '.$unread.' unread' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        @if ($unread)
            <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>
    <div x-cloak x-show="open" @click.outside="open = false" x-transition.origin.top.right
         class="fixed inset-x-4 top-16 z-50 overflow-hidden rounded-xl bg-white shadow-lift ring-1 ring-gray-200 sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-[22rem]">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <p class="text-sm font-semibold text-gray-900">Notifications</p>
            @if ($unread)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="text-xs font-medium text-brand-600 hover:text-brand-700">Mark all as read</button>
                </form>
            @endif
        </div>
        @if ($latest->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-gray-500">You're all caught up.</p>
        @else
            <div class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
                @foreach ($latest as $notification)
                    @include('notifications._item', ['compact' => true])
                @endforeach
            </div>
        @endif
        <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-2.5 text-center text-sm font-medium text-gray-600 hover:bg-gray-50">See all</a>
    </div>
</div>

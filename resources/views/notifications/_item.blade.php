@php($data = $notification->data)
<form method="POST" action="{{ route('notifications.open', $notification->id) }}">
    @csrf
    <button class="flex w-full gap-3 px-4 py-3 text-left transition hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-brand-50/40' }}">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-brand-100 text-brand-700' }}">
            <x-ui.icon :name="$data['icon'] ?? 'info'" class="h-4 w-4" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-gray-900">{{ $data['title'] ?? 'Notification' }}</span>
            <span class="mt-0.5 block text-sm text-gray-600 {{ $compact ?? false ? 'line-clamp-2' : '' }}">{{ $data['body'] ?? '' }}</span>
            <span class="mt-1 block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
        </span>
        @unless ($notification->read_at)
            <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand-600" aria-label="Unread"></span>
        @endunless
    </button>
</form>

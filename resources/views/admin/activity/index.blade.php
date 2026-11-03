<x-admin-layout title="Activity log">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Every admin decision is recorded here and can't be edited.</p>
        <form method="GET" class="flex items-center gap-2">
            <label for="action" class="sr-only">Filter by action</label>
            <select id="action" name="action" onchange="this.form.submit()" class="form-control w-auto py-2 text-sm">
                <option value="">All actions</option>
                @foreach (\App\Models\AuditLog::ACTIONS as $key => $label)
                    <option value="{{ $key }}" @selected($action === $key)>{{ ucfirst($label) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-ui.card padding="p-0">
        @if ($logs->isEmpty())
            <x-ui.empty-state icon="clipboard-check" title="Nothing recorded yet" class="border-0" />
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($logs as $log)
                    <li class="flex gap-4 px-5 py-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600">
                            {{ mb_strtoupper(mb_substr($log->user?->name ?? '?', 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="text-gray-900">
                                <span class="font-semibold">{{ $log->user?->name ?? 'Deleted user' }}</span>
                                {{ $log->description() }}
                                @if ($log->subject_id) <span class="text-gray-500">#{{ $log->subject_id }}</span> @endif
                            </p>
                            @if ($log->details)
                                <p class="mt-1 truncate text-gray-500">
                                    @foreach ($log->details as $key => $value)
                                        <span class="mr-3"><span class="text-gray-400">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</span>
                                    @endforeach
                                </p>
                            @endif
                        </div>
                        <time class="shrink-0 text-xs text-gray-400" datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('M j, Y H:i:s') }}">{{ $log->created_at->diffForHumans() }}</time>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
    <div class="mt-6">{{ $logs->links() }}</div>
</x-admin-layout>

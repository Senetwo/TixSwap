<x-admin-layout title="Users">
    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">User</th>
                        <th class="table-th">Role</th>
                        <th class="table-th">Joined</th>
                        <th class="table-th"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50/60">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs font-normal text-gray-400">(you)</span>@endif</p>
                                        <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <x-ui.badge :tone="['admin' => 'brand', 'seller' => 'blue'][$user->role] ?? 'gray'">{{ ucfirst($user->role) }}</x-ui.badge>
                            </td>
                            <td class="table-td whitespace-nowrap text-gray-500">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="table-td text-right">
                                @unless ($user->isAdmin() || $user->is(auth()->user()))
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($user->name) }}\'s account? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600" aria-label="Delete {{ $user->name }}"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    <div class="mt-6">{{ $users->links() }}</div>
</x-admin-layout>

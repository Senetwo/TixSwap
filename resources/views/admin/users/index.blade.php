<x-admin-layout>
    <x-slot name="title">User Management</x-slot>
    <h1 class="text-2xl font-bold mb-6">All Users</h1>
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ ucfirst($user->role) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">New User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4 rounded-sm py-2">
            <span class="text-xs">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-4 rounded-sm py-2">
            <span class="text-xs">{{ session('error') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full">
                <thead>
                    <tr class="bg-base-200">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="hover">
                            <td class="font-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-outline badge-xs {{ $user->isSuperAdmin() ? 'badge-error' : ($user->isAdmin() ? 'badge-warning' : 'badge-ghost') }}">
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                            </td>
                            <td class="text-[11px]">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-xs text-info p-1 h-auto min-h-0">Edit</a>
                                    
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error p-1 h-auto min-h-0">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 opacity-50">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 border-t border-base-200">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>

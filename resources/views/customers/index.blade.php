<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customers') }}
            </h2>
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">Add Customer</a>
        </div>
    </x-slot>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 md:p-6">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th class="hidden md:table-cell">Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div class="font-bold">{{ $customer->name }}</div>
                                </td>
                                <td>
                                    <div>{{ $customer->phone }}</div>
                                    <div class="text-xs opacity-60">{{ $customer->email }}</div>
                                </td>
                                <td class="hidden md:table-cell truncate max-w-xs">{{ $customer->address }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost btn-xs text-primary">Edit</a>
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost btn-xs">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Customers</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">Add Customer</a>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full">
                <thead>
                    <tr class="bg-base-200">
                        <th>Name</th>
                        <th>Contact</th>
                        <th class="hidden md:table-cell">Address</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="hover">
                            <td>
                                <div class="font-bold text-[11px]">{{ $customer->name }}</div>
                            </td>
                            <td>
                                <div class="text-[11px] font-semibold">{{ $customer->phone }}</div>
                                <div class="text-[10px] opacity-60">{{ $customer->email }}</div>
                            </td>
                            <td class="hidden md:table-cell truncate max-w-xs text-[11px]">{{ $customer->address }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost btn-xs p-1 h-auto min-h-0">View</a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost btn-xs text-primary p-1 h-auto min-h-0">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 opacity-50">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 border-t border-base-200">
            {{ $customers->links() }}
        </div>
    </div>
</x-app-layout>

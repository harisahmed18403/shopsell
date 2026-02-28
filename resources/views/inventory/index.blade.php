<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Physical Inventory</h1>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">Add New Item</a>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 mb-4">
        <div class="p-3">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" placeholder="Search by name or IMEI..." class="input input-bordered input-sm flex-1 min-w-[200px]" value="{{ request('search') }}" />
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full">
                <thead>
                    <tr class="bg-base-200">
                        <th>Product</th>
                        <th>IMEI / Serial</th>
                        <th>Condition</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="hover">
                            <td>
                                <div class="font-bold">{{ $item->product?->name ?? 'Unknown Product' }}</div>
                                <div class="text-[10px] opacity-50">{{ $item->product?->category?->name }}</div>
                            </td>
                            <td><span class="font-mono text-[11px]">{{ $item->imei ?? 'N/A' }}</span></td>
                            <td><span class="text-[11px]">{{ $item->condition ?? 'N/A' }}</span></td>
                            <td>£{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="font-bold text-primary">£{{ number_format($item->sale_price, 2) }}</td>
                            <td>
                                <span class="badge {{ $item->status == 'available' ? 'badge-success' : 'badge-ghost' }} badge-xs">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td><span class="text-[10px] opacity-60">{{ $item->created_at->format('d M Y') }}</span></td>
                            <td class="text-right">
                                <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from inventory?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error p-1 h-auto min-h-0">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 opacity-50">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 border-t border-base-200">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>

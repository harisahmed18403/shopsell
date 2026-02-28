<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">New Transaction</a>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 mb-4">
        <div class="p-3">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-2">
                <select name="type" class="select select-bordered select-sm min-w-[120px]" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="sell" {{ request('type') == 'sell' ? 'selected' : '' }}>Sell</option>
                    <option value="buy" {{ request('type') == 'buy' ? 'selected' : '' }}>Buy</option>
                    <option value="repair" {{ request('type') == 'repair' ? 'selected' : '' }}>Repair</option>
                </select>
                <select name="status" class="select select-bordered select-sm min-w-[120px]" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full">
                <thead>
                    <tr class="bg-base-200">
                        <th>Date</th>
                        <th>Type</th>
                        <th>Products</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="hover">
                            <td class="text-[11px]">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-outline badge-xs {{ $transaction->type == 'sell' ? 'border-primary text-primary' : ($transaction->type == 'buy' ? 'border-secondary text-secondary' : 'border-accent text-accent') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="text-[11px] max-w-[200px] truncate">
                                @foreach($transaction->items as $item)
                                    {{ $item->product?->name ?? $item->description }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </td>
                            <td class="text-[11px]">{{ $transaction->customer?->name ?? 'Guest' }}</td>
                            <td class="font-bold">£{{ number_format($transaction->total_amount, 2) }}</td>
                            <td><span class="badge badge-xs badge-success">{{ $transaction->status }}</span></td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-ghost btn-xs text-primary p-1 h-auto min-h-0">Details</a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-ghost btn-xs text-info p-1 h-auto min-h-0">Edit</a>
                                    <a href="{{ route('transactions.invoice', $transaction) }}" class="btn btn-ghost btn-xs text-success p-1 h-auto min-h-0" target="_blank">Invoice</a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction? This will reverse any stock changes.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-error p-1 h-auto min-h-0">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 opacity-50">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 border-t border-base-200">
            {{ $transactions->links() }}
        </div>
    </div>
</x-app-layout>

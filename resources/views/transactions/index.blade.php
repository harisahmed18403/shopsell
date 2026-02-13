<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transactions') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">New Transaction</a>
        </div>
    </x-slot>

    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-4">
                <div class="form-control">
                    <select name="type" class="select select-bordered" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="sell" {{ request('type') == 'sell' ? 'selected' : '' }}>Sell</option>
                        <option value="buy" {{ request('type') == 'buy' ? 'selected' : '' }}>Buy</option>
                        <option value="repair" {{ request('type') == 'repair' ? 'selected' : '' }}>Repair</option>
                    </select>
                </div>
                <div class="form-control">
                    <select name="status" class="select select-bordered" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 md:p-6">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-outline {{ $transaction->type == 'sell' ? 'border-primary text-primary' : ($transaction->type == 'buy' ? 'border-secondary text-secondary' : 'border-accent text-accent') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->customer?->name ?? 'Guest' }}</td>
                                <td>£{{ number_format($transaction->total_amount, 2) }}</td>
                                <td><span class="badge badge-sm badge-success">{{ $transaction->status }}</span></td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-ghost btn-xs text-primary">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

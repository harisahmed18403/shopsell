<x-app-layout>
    <!-- Quick Price Search -->
    <div class="mb-6">
        <x-product-search />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stats shadow bg-base-100 rounded-sm">
            <div class="stat p-3">
                <div class="stat-title text-xs uppercase font-bold opacity-60">Daily Sales</div>
                <div class="stat-value text-2xl text-primary">£{{ number_format($dailySales, 2) }}</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100 rounded-sm">
            <div class="stat p-3">
                <div class="stat-title text-xs uppercase font-bold opacity-60">Weekly Sales</div>
                <div class="stat-value text-2xl text-secondary">£{{ number_format($weeklySales, 2) }}</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100 rounded-sm">
            <div class="stat p-3">
                <div class="stat-title text-xs uppercase font-bold opacity-60">Monthly Sales</div>
                <div class="stat-value text-2xl text-accent">£{{ number_format($monthlySales, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Recent Transactions -->
        <div class="card bg-base-100 shadow rounded-sm border border-base-300">
            <div class="card-body p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-sm uppercase tracking-wider opacity-70">Recent Transactions</h2>
                    <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-xs underline text-primary">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-xs w-full">
                        <thead>
                            <tr class="bg-base-200">
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr class="hover">
                                    <td>{{ $transaction->created_at->format('d M, H:i') }}</td>
                                    <td>{{ $transaction->customer?->name ?? 'Guest' }}</td>
                                    <td class="font-bold">£{{ number_format($transaction->total_amount, 2) }}</td>
                                    <td><span class="badge badge-success badge-xs">{{ $transaction->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card bg-base-100 shadow rounded-sm border border-base-300">
            <div class="card-body p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-sm uppercase tracking-wider text-error">Low Stock Alert</h2>
                    <a href="{{ route('products.index') }}" class="btn btn-ghost btn-xs underline">Inventory</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-xs w-full">
                        <thead>
                            <tr class="bg-base-200">
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr class="hover">
                                    <td class="max-w-[150px] truncate">{{ $product->name }}</td>
                                    <td><span class="text-error font-bold">{{ $product->quantity }}</span></td>
                                    <td>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-xs text-primary underline p-0 h-auto min-h-0">Restock</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

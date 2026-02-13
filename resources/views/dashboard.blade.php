<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Quick Price Search -->
    <div class="mb-8">
        <h3 class="text-center text-lg font-bold mb-4 opacity-70 italic">Quick Market Price Lookup</h3>
        <x-product-search />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Daily Sales</div>
                <div class="stat-value text-primary">£{{ number_format($dailySales, 2) }}</div>
                <div class="stat-desc">Today's total revenue</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Weekly Sales</div>
                <div class="stat-value text-secondary">£{{ number_format($weeklySales, 2) }}</div>
                <div class="stat-desc">This week's total revenue</div>
            </div>
        </div>

        <div class="stats shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">Monthly Sales</div>
                <div class="stat-value text-accent">£{{ number_format($monthlySales, 2) }}</div>
                <div class="stat-desc">This month's total revenue</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title mb-4">Recent Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M, H:i') }}</td>
                                    <td>{{ $transaction->customer?->name ?? 'Guest' }}</td>
                                    <td>£{{ number_format($transaction->total_amount, 2) }}</td>
                                    <td><span class="badge badge-success badge-sm">{{ $transaction->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('transactions.index') }}" class="btn btn-primary btn-sm">View All</a>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title mb-4 text-error">Low Stock Alert</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td><span class="text-error font-bold">{{ $product->quantity }}</span></td>
                                    <td>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-xs text-primary underline">Restock</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">Inventory</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

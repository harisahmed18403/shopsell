<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">Edit</a>
                <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Product Information</h3>
                    <div class="flex justify-between gap-6">
                        <div class="flex-1">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs opacity-60 uppercase font-bold">Category</label>
                                    <div>{{ $product->category?->name }}</div>
                                </div>
                                <div>
                                    <label class="text-xs opacity-60 uppercase font-bold">Grade</label>
                                    <div>{{ $product->grade }}</div>
                                </div>
                                <div>
                                    <label class="text-xs opacity-60 uppercase font-bold">Color</label>
                                    <div>{{ $product->color }}</div>
                                </div>
                                <div>
                                    <label class="text-xs opacity-60 uppercase font-bold">Current Stock</label>
                                    <div><span class="badge {{ $product->quantity < 5 ? 'badge-error' : 'badge-success' }}">{{ $product->quantity }}</span></div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="text-xs opacity-60 uppercase font-bold">Description</label>
                                <p class="line-clamp-3">{{ $product->description ?? 'No description provided.' }}</p>
                            </div>
                        </div>
                        @if($product->cexProducts->isNotEmpty() && $product->cexProducts->first()->image_url)
                            <div class="w-40 flex-shrink-0">
                                <img src="{{ $product->cexProducts->first()->image_url }}" alt="{{ $product->name }}" class="rounded-xl w-full h-full max-h-48 object-contain bg-white border border-base-200 shadow-sm" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CeX Market Prices -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2 flex justify-between items-center">
                        CeX Market Prices
                        @if($product->cexProducts->isNotEmpty())
                            <span class="text-xs font-normal opacity-60">Last synced: {{ $product->cexProducts->first()->last_synced_at?->diffForHumans() }}</span>
                        @endif
                    </h3>
                    
                    @if($product->cexProducts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>CeX Name</th>
                                        <th>Grade</th>
                                        <th>Sale</th>
                                        <th>Cash</th>
                                        <th>Voucher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->cexProducts as $cex)
                                        <tr>
                                            <td class="text-xs">{{ $cex->name }}</td>
                                            <td><span class="badge badge-outline">{{ $cex->grade }}</span></td>
                                            <td class="font-bold">£{{ number_format($cex->sale_price, 2) }}</td>
                                            <td class="text-error">£{{ number_format($cex->cash_price, 2) }}</td>
                                            <td class="text-success">£{{ number_format($cex->voucher_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 opacity-60 italic">
                            No CeX data available for this product. Run sync to fetch prices.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pricing & Quick Actions -->
        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Your Pricing</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span>Sale Price</span>
                            <span class="text-2xl font-bold text-primary">£{{ number_format($product->sale_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Cash Price</span>
                            <span class="font-semibold text-error">£{{ number_format($product->cash_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Voucher Price</span>
                            <span class="font-semibold text-success">£{{ number_format($product->voucher_price, 2) }}</span>
                        </div>
                    </div>
                    <div class="card-actions mt-6">
                        <button class="btn btn-primary w-full">Quick Sell</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70 truncate max-w-md">{{ $product->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">Edit</a>
            <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Product Information</h3>
                    <div class="flex justify-between gap-6">
                        <div class="flex-1">
                            <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div>
                                    <label class="text-[10px] opacity-60 uppercase font-bold block">Category</label>
                                    <div class="text-sm">{{ $product->category?->name }}</div>
                                </div>
                                <div>
                                    <label class="text-[10px] opacity-60 uppercase font-bold block">Grade</label>
                                    <div class="text-sm">{{ $product->grade }}</div>
                                </div>
                                <div>
                                    <label class="text-[10px] opacity-60 uppercase font-bold block">Color</label>
                                    <div class="text-sm">{{ $product->color }}</div>
                                </div>
                                <div>
                                    <label class="text-[10px] opacity-60 uppercase font-bold block">Stock</label>
                                    <div><span class="badge {{ $product->quantity < 5 ? 'badge-error' : 'badge-success' }} badge-xs">{{ $product->quantity }}</span></div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="text-[10px] opacity-60 uppercase font-bold block">Description</label>
                                <p class="text-sm opacity-80 leading-relaxed">{{ $product->description ?? 'No description provided.' }}</p>
                            </div>
                        </div>
                        @if($product->cexProducts->isNotEmpty() && $product->cexProducts->first()->image_url)
                            <div class="w-32 flex-shrink-0">
                                <img src="{{ $product->cexProducts->first()->image_url }}" alt="{{ $product->name }}" class="rounded border border-base-200 w-full h-32 object-contain bg-white shadow-sm" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CeX Market Prices -->
            <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
                <div class="card-body p-4">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-xs uppercase opacity-60">CeX Market Prices</h3>
                        @if($product->cexProducts->isNotEmpty())
                            <span class="text-[10px] font-normal opacity-50 italic">Synced: {{ $product->cexProducts->first()->last_synced_at?->diffForHumans() }}</span>
                        @endif
                    </div>
                    
                    @if($product->cexProducts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="table table-xs w-full">
                                <thead>
                                    <tr class="bg-base-200">
                                        <th>Variant Name</th>
                                        <th class="text-center">Grade</th>
                                        <th>Sale</th>
                                        <th>Cash</th>
                                        <th>Voucher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->cexProducts as $cex)
                                        <tr class="hover">
                                            <td class="text-[11px] font-medium">{{ $cex->name }}</td>
                                            <td class="text-center"><span class="badge badge-outline badge-xs">{{ $cex->grade }}</span></td>
                                            <td class="font-bold text-primary">£{{ number_format($cex->sale_price, 2) }}</td>
                                            <td class="text-error font-semibold">£{{ number_format($cex->cash_price, 2) }}</td>
                                            <td class="text-success font-semibold">£{{ number_format($cex->voucher_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 opacity-50 text-xs italic">
                            No CeX data available for this product.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pricing & Quick Actions -->
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
                <div class="card-body p-4">
                    <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Your Pricing</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold opacity-70">Sale Price</span>
                            <span class="text-xl font-bold text-primary">£{{ number_format($product->sale_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold opacity-70">Cash Price</span>
                            <span class="font-bold text-error">£{{ number_format($product->cash_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold opacity-70">Voucher Price</span>
                            <span class="font-bold text-success">£{{ number_format($product->voucher_price, 2) }}</span>
                        </div>
                    </div>
                    <div class="card-actions mt-6">
                        <button class="btn btn-primary btn-sm w-full">Quick Sell</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

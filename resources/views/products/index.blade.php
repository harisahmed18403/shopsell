<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Products</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add Product</a>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 mb-4">
        <div class="p-3">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" placeholder="Search products..." class="input input-bordered input-sm flex-1 min-w-[200px]" value="{{ request('search') }}" />
                <select name="category_id" class="select select-bordered select-sm min-w-[150px]" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full">
                <thead>
                    <tr class="bg-base-200">
                        <th class="w-12 text-center">Img</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="hidden md:table-cell">Color</th>
                        <th class="text-center">Stock</th>
                        <th>Price</th>
                        <th>CeX Market</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="hover">
                            <td class="text-center">
                                <div class="avatar">
                                    <div class="w-8 h-8 rounded border border-base-200 overflow-hidden bg-white">
                                        @php
                                            $cexImage = $product->cexProducts->first()?->image_url;
                                        @endphp
                                        <img src="{{ $cexImage ?? 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}" class="object-contain w-full h-full" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="font-bold hover:underline block truncate max-w-[200px]">
                                    {{ $product->name }}
                                </a>
                                <div class="text-[10px] opacity-50 md:hidden">{{ $product->category?->name }}</div>
                            </td>
                            <td><span class="text-[11px]">{{ $product->category?->name }}</span></td>
                            <td class="hidden md:table-cell text-[11px]">{{ $product->color }}</td>
                            <td class="text-center">
                                <span class="badge {{ $product->quantity < 5 ? 'badge-error' : 'badge-ghost' }} badge-xs font-mono">
                                    {{ $product->quantity }}
                                </span>
                            </td>
                            <td class="font-bold">£{{ number_format($product->sale_price, 2) }}</td>
                            <td>
                                @if($product->cexProducts->isNotEmpty())
                                    @php
                                        $bestVariant = $product->cexProducts->where('grade', 'B')->first() ?? $product->cexProducts->first();
                                    @endphp
                                    <div class="text-[10px] leading-tight">
                                        <div class="font-bold text-primary">S: £{{ number_format($bestVariant->sale_price, 0) }}</div>
                                        <div class="text-error">C: £{{ number_format($bestVariant->cash_price, 0) }}</div>
                                    </div>
                                @else
                                    <span class="text-[10px] opacity-40 italic">No data</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-ghost btn-xs p-1 h-auto min-h-0">View</a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-xs text-primary p-1 h-auto min-h-0">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-error p-1 h-auto min-h-0">Del</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 opacity-50">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 border-t border-base-200">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products') }}
            </h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add Product</a>
        </div>
    </x-slot>

    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-4">
                <div class="form-control w-full max-w-xs">
                    <input type="text" name="search" placeholder="Search products..." class="input input-bordered w-full" value="{{ request('search') }}" />
                </div>
                <div class="form-control w-full max-w-xs">
                    <select name="category_id" class="select select-bordered w-full" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-ghost">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0 md:p-6">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="hidden md:table-cell">Color</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Market (CeX)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="avatar">
                                        <div class="mask mask-squircle w-12 h-12">
                                            @php
                                                $cexImage = $product->cexProducts->first()?->image_url;
                                            @endphp
                                            <img src="{{ $cexImage ?? 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="hover:underline">
                                        <div class="font-bold">{{ $product->name }}</div>
                                    </a>
                                    <div class="text-xs opacity-50 md:hidden">{{ $product->category?->name }}</div>
                                </td>
                                <td>{{ $product->category?->name }}</td>
                                <td class="hidden md:table-cell">{{ $product->color }}</td>
                                <td>
                                    <span class="badge {{ $product->quantity < 5 ? 'badge-error' : 'badge-ghost' }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>
                                <td>£{{ number_format($product->sale_price, 2) }}</td>
                                <td>
                                    @if($product->cexProducts->isNotEmpty())
                                        @php
                                            $bestVariant = $product->cexProducts->where('grade', 'B')->first() ?? $product->cexProducts->first();
                                        @endphp
                                        <div class="text-xs">
                                            <div class="font-bold text-primary">S: £{{ number_format($bestVariant->sale_price, 2) }}</div>
                                            <div class="text-error">C: £{{ number_format($bestVariant->cash_price, 2) }}</div>
                                        </div>
                                    @else
                                        <span class="text-xs opacity-40 italic">No data</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-ghost btn-xs">View</a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-xs text-primary">Edit</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs text-error">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

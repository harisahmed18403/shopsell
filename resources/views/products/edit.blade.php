<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Edit Product: {{ $product->name }}</h1>
        <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="form-control w-full">
                        <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Product Name</span></label>
                        <input type="text" name="name" class="input input-bordered input-sm w-full" value="{{ old('name', $product->name) }}" required />
                    </div>

                    <div class="form-control w-full mt-3">
                        <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Category</span></label>
                        <select name="category_id" class="select select-bordered select-sm w-full" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Sale Price</span></label>
                            <input type="number" step="0.01" name="sale_price" class="input input-bordered input-sm" value="{{ old('sale_price', $product->sale_price) }}" />
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Cash Price</span></label>
                            <input type="number" step="0.01" name="cash_price" class="input input-bordered input-sm" value="{{ old('cash_price', $product->cash_price) }}" />
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Voucher Price</span></label>
                            <input type="number" step="0.01" name="voucher_price" class="input input-bordered input-sm" value="{{ old('voucher_price', $product->voucher_price) }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Color</span></label>
                            <input type="text" name="color" class="input input-bordered input-sm" value="{{ old('color', $product->color) }}" />
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Grade</span></label>
                            <input type="text" name="grade" class="input input-bordered input-sm" value="{{ old('grade', $product->grade) }}" />
                        </div>
                    </div>

                    <div class="form-control mt-3">
                        <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Quantity</span></label>
                        <input type="number" name="quantity" class="input input-bordered input-sm" value="{{ old('quantity', $product->quantity) }}" />
                    </div>

                    <div class="form-control mt-3">
                        <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Description</span></label>
                        <textarea name="description" class="textarea textarea-bordered textarea-sm h-20">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="card-actions justify-end mt-6">
                        <button type="submit" class="btn btn-primary btn-sm px-8">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

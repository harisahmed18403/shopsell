<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Product') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Product Name</span></label>
                        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name') }}" required />
                    </div>

                    <div class="form-control w-full mt-4">
                        <label class="label"><span class="label-text">Category</span></label>
                        <select name="category_id" class="select select-bordered w-full" required>
                            <option value="" disabled selected>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Sale Price</span></label>
                            <input type="number" step="0.01" name="sale_price" class="input input-bordered" value="{{ old('sale_price') }}" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Cash Price</span></label>
                            <input type="number" step="0.01" name="cash_price" class="input input-bordered" value="{{ old('cash_price') }}" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Voucher Price</span></label>
                            <input type="number" step="0.01" name="voucher_price" class="input input-bordered" value="{{ old('voucher_price') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Color</span></label>
                            <input type="text" name="color" class="input input-bordered" value="{{ old('color') }}" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Grade/Condition</span></label>
                            <input type="text" name="grade" class="input input-bordered" placeholder="e.g. A, B, New" value="{{ old('grade') }}" />
                        </div>
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Initial Quantity</span></label>
                        <input type="number" name="quantity" class="input input-bordered" value="{{ old('quantity', 0) }}" />
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Description</span></label>
                        <textarea name="description" class="textarea textarea-bordered h-24">{{ old('description') }}</textarea>
                    </div>

                    <div class="card-actions justify-end mt-6">
                        <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
